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
    private bool $readOnly = false;

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
            $this->sqliteConfigure($sqliteDsn);
        }

        if (Str::startsWith(Str::upper($this->sql), ['SELECT', 'PRAGMA'])) {
            $this->readOnly = true;
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

    private function sqliteConfigure(string $sqliteDsn): void
    {
        $this->sqliteConnection = new BasePDO(
            $sqliteDsn,
            null,
            null,
            [BasePDO::SQLITE_ATTR_OPEN_FLAGS => BasePDO::SQLITE_OPEN_READONLY]
        );

        $stmt = $this->sqliteConnection->prepare(<<<SQL
                        PRAGMA synchronous = NORMAL;
                        PRAGMA mmap_size = 134217728;
                        PRAGMA cache_size = -20000;
                        PRAGMA foreign_keys = true;
                        PRAGMA busy_timeout = 5000;
                        PRAGMA temp_store = memory;
                        SQL);

        $stmt->execute();
    }

    public function bindValue(int|string $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->parameterizedMap[] = $value;

        return true;
    }

    public function execute(array|null $params = null): bool
    {
        if ($this->useSQLite()) {
            try {
                $this->requestRQLiteBySQLite($params);
                return true;
            } catch (PDOException $e) {
                if (app()->hasDebugModeEnabled()) {
                    /*
                     * SQLite queries will occasionally experience 'trying to write to read-only database' errors.
                     * That's because we don't allow PHP write operations on the SQLite database. See README.md.
                     *
                     * When this, or any other error occurs, we quietly fall back to the RQLite HTTP request method.
                     *
                     * Failures are logged when debugging is enabled.
                     */
                    Log::info($e->getMessage());
                }
            }
        }

        $this->requestRQLiteByHttp();
        return true;
    }

    public function request(): array
    {
        if ($this->useSQLite()) {
            try {
                return $this->requestRQLiteBySQLite();
            } catch (PDOException $e) {
                Log::info($e->getMessage());
            }
        }

        return $this->requestRQLiteByHttp();
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        $fetchMode = ($mode === PDO::FETCH_DEFAULT) ? $this->fetchMode : $mode;
        $results = $this->request();

        // SQLite returns an object
        if (isset($results[0]) && is_object($results[0])) {
            return $results;
        }

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

        if ($this->freshness) {
            $params .= '&freshness=' . $this->freshness;
        }
        if ($this->strictFreshness) {
            $params .= '&strict_freshness=' . $this->strictFreshness;
        }

        return $params;
    }

    private function buildExecParams(): string
    {
        $params = '?timings=true';

        if ($this->queuedWrites) {
            $params .= '&queue';
        }

        return $params;
    }

    private function buildQueryUrl(): string
    {
        if ($this->readOnly) {
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


    private function requestRQLitebySQLite(): array
    {
        try {
            $stmt = $this->sqliteConnection->prepare($this->sql);

            foreach ($this->parameterizedMap as $key => $value) {
                $stmt->bindValue(is_int($key) ? $key + 1 : $key, $value); // SQLite uses 1-based parameter indexing
            }

            $stmt->execute();

            return $stmt->fetchAll($this->fetchMode);
        } catch (PDOException $e) {
            throw new PDOException("SQLite request failed: " . $e->getMessage());
        }
    }

    private function requestRQLiteByHttp()
    {
        $jsonOptionData = json_encode($this->makeRequestData($this->sql, $this->parameterizedMap));

        curl_setopt($this->connection, CURLOPT_POSTFIELDS, $jsonOptionData);
        curl_setopt($this->connection, CURLOPT_URL, $this->buildQueryUrl());

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

    private function useSQLite(): bool
    {
        return $this->readOnly && $this->consistency === PDO::RQLITE_CONSISTENCY_NONE && $this->sqliteConnection;
    }
}
