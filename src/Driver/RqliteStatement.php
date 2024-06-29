<?php

namespace Wanwire\LaravelEloquentRqlite\Driver;

use Doctrine\DBAL\Driver\Result;

use Doctrine\DBAL\ParameterType;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDO;
use PDOException;
use PDOStatement;

//class RqliteStatement extends \PDOStatement implements \Doctrine\DBAL\Driver\Statement
class RqliteStatement extends PDOStatement implements \Doctrine\DBAL\Driver\Statement
{
    private string $sql;
    private PendingRequest $connection;
    public int $lastInsertId;

    private array $parameterizedMap = [];
    private int $fetchMode = PDO::FETCH_ASSOC;
    private ?string $fetchClassName = null;
    private array $fetchParams = [];

    public function __construct(string $sql, PendingRequest $connection)
    {
        $this->sql = $sql;
        $this->connection = $connection;
    }

    public function bindValue($param, $value, $type = ParameterType::STRING): bool
    {
        $this->parameterizedMap[] = $value;

        return true;
    }

    public function bindParam(
        $param,
        mixed &$variable,
        $type = PDO::PARAM_STR,
        $length = 0,
        mixed $driverOptions = null
    ): bool {
        return parent::bindParam(
            $param,
            $variable,
            $type,
            $length,
            $driverOptions
        ); // TODO: Change the autogenerated stub
    }

    #[\ReturnTypeWillChange]
    public function execute($params = null): Result
    {
        return new RqliteResult($this->requestRqliteByHttp());
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        $fetchMode = ($mode === PDO::FETCH_DEFAULT) ? $this->fetchMode : $mode;
        $results = $this->requestRqliteByHttp();

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

    private function instantiateFetchClass(array $row)
    {
        $className = $this->fetchClassName ?? 'stdClass';
        $params = $this->fetchParams;

        if ($params) {
            $reflectionClass = new \ReflectionClass($className);
            return $reflectionClass->newInstanceArgs($params);
        } else {
            return new $className($row);
        }
    }

    private function makeRequestData(string $sql, array $parameterizedMap): array
    {
        return [[$sql, ...$parameterizedMap]];
    }

    private function requestRqliteByHttp()
    {
        $retryCount = 3;
        $retryDelayMicroseconds = 250000; // 250 milliseconds
        $attempts = 0;

        while ($attempts < $retryCount) {
            try {
                if (Str::startsWith(Str::upper($this->sql), ['SELECT', 'PRAGMA'])) {
                    $uri = '/db/query?level=strong';
                } else {
                    $uri = '/db/execute';
                }

                $jsonOptionData = $this->makeRequestData($this->sql, $this->parameterizedMap);
                $response = $this->connection->post($uri, $jsonOptionData);
                if ($response->status() !== 200) {
                    dd($response);
                }
                $result = json_decode($response->body(), true);

                if (isset($result['results'])) {
                    collect($result['results'])->map(function ($item) {
                        if (isset($item['error'])) {
                            throw new PDOException($item['error']);
                        }
                    });
                }

                if (isset($result['results'][0]['last_insert_id'])) {
                    $this->lastInsertId = $result['results'][0]['last_insert_id'];
                }

                return $result['results'];
            } catch (PDOException $e) {
                if ($this->isReadOnlyDatabaseError($e)) {
                    $attempts++;
                    if ($attempts >= $retryCount) {
                        Log::error('Max retry attempts reached for readonly database error', ['exception' => $e]);
                        throw $e;
                    }
                    usleep($retryDelayMicroseconds); // Wait before retrying
                } else {
                    throw $e;
                }
            }
        }
    }

    protected function isReadOnlyDatabaseError(PDOException $e): bool
    {
        return str_contains($e->getMessage(), 'attempt to write a readonly database');
    }

    public function setFetchMode($mode, $className = null, ...$params): void
    {
        $this->fetchMode = $mode;
        $this->fetchClassName = $className;
        $this->fetchParams = $params;
    }


    // TODO: hack to fix delete
    public function rowCount(): int
    {
        return 0;
    }

}
