<?php

namespace Wanwire\RQLite\Connector;

use CurlHandle;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DBALStatement;
use Doctrine\DBAL\ParameterType;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use PDOException;
use Wanwire\RQLite\Driver\Statement;

final class DBALConnection implements \Doctrine\DBAL\Driver\Connection
{
    private CurlHandle $connection;
    private Statement $statement;

    private string $baseUrl;

    public function __construct(CurlHandle $connection, $params)
    {
        $this->connection = $connection;
        $this->baseUrl    = "http://{$params['host']}:{$params['port']}";
    }

    public function prepare(string $sql): DBALStatement
    {
        $this->statement = new Statement($sql, $this->connection, $this->baseUrl);
        return $this->statement;
    }

    public function query(string $sql): Result
    {
       return $this->prepare($sql)->execute();
    }

    public function quote(mixed $value, $type = ParameterType::STRING): float|int|string
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        $value = str_replace("'", "''", $value);

        return "'".addcslashes($value, "\000\n\r\\\032")."'";
    }

    public function exec(string $sql): int
    {
        return $this->prepare($sql)->execute()->rowCount();
    }

    public function lastInsertId($name = null): string|int|false
    {
        return $this->statement->lastInsertId;
    }

    public function beginTransaction()
    {
        throw new PDOException('BEGIN invalid for rqlite.');
    }

    public function commit()
    {
        throw new PDOException('COMMIT invalid for rqlite.');
    }

    public function rollBack()
    {
        throw new PDOException('ROLLBACK invalid for rqlite.');
    }

    public function getNativeConnection(): PendingRequest
    {
        return $this->connection;
    }

    public function transactionRaw(array $rqliteSqlLists): mixed
    {
        try {
            $res = $this->connection->post('/db/execute?transaction', ['json' => $rqliteSqlLists]);

            return $this->getResultOrFail($res);
        } catch (GuzzleException $e) {
        }
    }

    private function getResultOrFail(Response $res)
    {
        $result = json_decode($res->getBody(), true);
        if (isset($result['results'])) {
            collect($result['results'])->map(function ($item) {
                if (isset($item['error'])) {
                    throw new PDOException($item['error']);
                }
            });
        }

        return $result;
    }
}
