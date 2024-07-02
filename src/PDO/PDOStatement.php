<?php

namespace Wanwire\RQLite\PDO;

use Illuminate\Support\Facades\Log;
use PDO as BasePDO;
use CurlHandle;
use Illuminate\Support\Str;
use PDOException;
use PDOStatement as BasePDOStatement;
use Wanwire\RQLite\Exceptions\RQLiteDriverException;

class PDOStatement extends BasePDOStatement
{
    private string $sql;
    private CurlHandle $connection;
    private string $baseUrl;

    private string $consistency = 'strong';
    private ?int $freshness = null;
    private ?int $strictFreshness = null;
    private bool $queuedWrites = false;

    public ?int $lastInsertId = null;
    public ?int $rowsAffected = null;
    public ?float $executionTime = null;

    private array $parameterizedMap = [];
    private int $fetchMode = PDO::FETCH_ASSOC;
    private ?string $fetchClassName = null;
    private array $fetchParams = [];
    private ?BasePDO $sqliteConnection = null;

    public function __construct(string $sql, $connection, $baseUrl, $params, $sqliteDsn)
    {
        $this->sql = $sql;
        $this->connection = $connection;
        $this->baseUrl = $baseUrl;

        if (!empty($sqliteDsn)) {
            $this->sqliteConnection = new BasePDO($sqliteDsn, null, null, [BasePDO::SQLITE_ATTR_OPEN_FLAGS => BasePDO::SQLITE_OPEN_READONLY]);
        }

        if (isset($params['consistency'])) {
            $this->consistency = $params['consistency'];
        }
        if (isset($params['freshness'])) {
            $this->freshness = $params['freshness'];
        }
        if (isset($params['strict_freshness'])) {
            $this->strictFreshness = $params['strict_freshness'];
        }
        if (isset($params['queued_writes'])) {
            $this->queuedWrites = $params['queued_writes'];
        }
    }

    public function bindValue(int|string $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->parameterizedMap[] = $value;

        return true;
    }

    public function execute(array|null $params = null): bool
    {
        if(Str::startsWith(Str::upper($this->sql), ['SELECT', 'PRAGMA'])
            && $this->consistency === PDO::RQLITE_CONSISTENCY_NONE
            && $this->sqliteConnection) {
            try {
                $this->requestRQLiteBySQLite($params);

                if(! app()->isProduction()) {
                    Log::info('Request successfully executed by SQLite: ' . $this->sql);
                }

                return true;
            } catch (PDOException $e) {

                Log::info($e->getMessage());

            }
        }

        $this->requestRQLiteByHttp();
        return true;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        $fetchMode = ($mode === PDO::FETCH_DEFAULT) ? $this->fetchMode : $mode;
        $results = $this->requestRQLiteByHttp();

        if (empty($results)) {
            return [];
        }

        $results = $results[0];
        $tmp = [];

        if (isset($results['values'])) {
            foreach ($results['values'] as $key => $item) {
                foreach ($results['columns'] as $k => $i) {
                    if ($fetchMode === PDO::FETCH_OBJ) {
                        $tmp[$key][$i] = $item[$k];
                    } else {
                        $tmp[$key][$i] = $item[$k];
                    }
                }
                if ($fetchMode === PDO::FETCH_OBJ) {
                    $tmp[$key] = (object)$tmp[$key];
                } elseif ($fetchMode === PDO::FETCH_CLASS) {
                    $tmp[$key] = $this->instantiateFetchClass($tmp[$key]);
                }
            }
        }

        return $tmp;
    }

    private function makeRequestData(string $sql, array $parameterizedMap): array
    {
        return [[$sql, ...$parameterizedMap]];
    }

    private function buildQueryParams(): string
    {
        $params = '?level=' . $this->consistency . '&timings=true';

        if($this->freshness) {
            $params .= '&freshness=' . $this->freshness;
        }
        if($this->strictFreshness) {
            $params .= '&strict_freshness=' . $this->strictFreshness;
        }

        return $params;
    }

    private function buildExecParams(): string
    {
        $params = '?timings=true';

        if($this->queuedWrites) {
            $params .= '&queue';
        }

        return $params;
    }

    private function buildQueryUrl(): string
    {
        if (Str::startsWith(Str::upper($this->sql), ['SELECT', 'PRAGMA'])) {
            return $this->baseUrl . '/db/query' . $this->buildQueryParams();
        } else {
            return $this->baseUrl . '/db/execute' . $this->buildExecParams();
        }
    }

    private function processQueryResults($result): void
    {
        if (isset($result['results'][0]['last_insert_id'])) {
            $this->lastInsertId = $result['results'][0]['last_insert_id'];
        }

        if (isset($result['results'][0]['rows_affected'])) {
            $this->rowsAffected = $result['results'][0]['rows_affected'];
        }

        if (isset($result['results']['time'])) {
            $this->executionTime = $result['results']['time'];
        }
    }


    private function requestRQLitebySQLite($params): false|array
    {
        try {
            $stmt = $this->sqliteConnection->prepare($this->sql);

//            foreach ($params as $key => $value) {
//                $stmt->bindValue($key, $value);
//            }

            $stmt->execute();
            return $stmt->fetchAll(BasePDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("SQLite request failed: " . $e->getMessage());
        }
    }

    private function requestRQLiteByHttp()
    {
        $jsonOptionData = json_encode($this->makeRequestData($this->sql, $this->parameterizedMap));

        curl_setopt($this->connection, CURLOPT_POSTFIELDS, $jsonOptionData);
        curl_setopt($this->connection, CURLOPT_URL,  $this->buildQueryUrl());

        $response = curl_exec($this->connection);
        $httpCode = curl_getinfo($this->connection, CURLINFO_HTTP_CODE);
        if ($response === false || $httpCode !== 200) {
            $error = curl_error($this->connection);
            curl_close($this->connection);
            throw new RQLiteDriverException("cURL request failed with error: $error and HTTP code: $httpCode");
        }

        $result = json_decode($response, true);

        if (isset($result['results'])) {
            collect($result['results'])->map(function ($item) {
                if (isset($item['error'])) {
                    throw new PDOException($item['error']);
                }
            });
        }

        if(! app()->isProduction()) {
            Log::info('Request successfully executed by RQLite: ' . $this->sql);
        }

        $this->processQueryResults($result);

        return $result['results'];
    }

    public function setFetchMode($mode, $className = null, ...$params): void
    {
        $this->fetchMode = $mode;
        $this->fetchClassName = $className;
        $this->fetchParams = $params;
    }

    public function rowCount(): int
    {
        return $this->rowsAffected ?: 0;
    }

}
