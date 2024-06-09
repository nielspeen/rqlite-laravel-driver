<?php

namespace Wanwire\LaravelEloquentRqlite\Driver;

use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Wanwire\LaravelEloquentRqlite\Connector\Connection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RqliteDriver extends AbstractSQLiteDriver
{
    public function connect(array $params): Connection
    {
        $connection = $this->createConnection($params);

        return new Connection($connection);
    }


    private function createConnection(array $params): PendingRequest
    {
        if (!empty($params['username'])) {
            return Http::baseUrl("http://{$params['host']}:{$params['port']}")->withBasicAuth($params['username'], $params['password']);
        }

        return Http::baseUrl("http://{$params['host']}:{$params['port']}");
    }
}
