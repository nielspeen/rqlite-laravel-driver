<?php

namespace Wanwire\LaravelEloquentRQLite\Driver;

use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Exception;
use Wanwire\LaravelEloquentRQLite\Connector\Connection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RQLiteDriver extends AbstractSQLiteDriver
{

    public function connect(array $params): Connection
    {
        $connection = $this->createConnection($params);

        return new Connection($connection);
    }


    private function createConnection(array $params): PendingRequest
    {
        $connectTimeout = 3;
        $timeout = 15;
        $retries = 15;
        $backoff = 250;

        if(!app()->isProduction()) {
            $connectTimeout = 1;
            $timeout = 3;
            $retries = 2;
            $backoff = 100;
        }

        if (!empty($params['username'])) {
            return Http::connectTimeout($connectTimeout)
                ->withHeader('Connection', 'keep-alive')
                ->timeout($timeout)
                ->retry($retries, function (int $attempt, Exception $exception) use($backoff) {
                    return $attempt * $backoff;
                })->baseUrl("http://{$params['host']}:{$params['port']}")->withBasicAuth(
                    $params['username'],
                    $params['password']
                );
        }

        return Http::connectTimeout($connectTimeout)
            ->withHeader('Connection', 'keep-alive')
            ->timeout($timeout)
            ->retry($retries, function (int $attempt, Exception $exception) use($backoff) {
                return $attempt * $backoff;
            })->baseUrl("http://{$params['host']}:{$params['port']}");
    }

}
