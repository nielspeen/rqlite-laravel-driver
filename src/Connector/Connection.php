<?php

namespace Wanwire\LaravelEloquentRQLite\Connector;

use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Response;
use Wanwire\LaravelEloquentRQLite\Driver\RQLiteStatement;
use Illuminate\Http\Client\PendingRequest;
use PDOException;

final class Connection implements \Doctrine\DBAL\Driver\Connection
{
    private PendingRequest $connection;
    private RQLiteStatement $statement;

    public function __construct(PendingRequest $connection)
    {
        $this->connection = $connection;
    }

    public function prepare(string $sql): Statement
    {
        $this->statement = new RQLiteStatement($sql, $this->connection);
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
