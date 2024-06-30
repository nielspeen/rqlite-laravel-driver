<?php

namespace Wanwire\LaravelEloquentRQLite\Driver;

use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Wanwire\LaravelEloquentRQLite\Connector\Connection;

class RQLiteDriver extends AbstractSQLiteDriver
{

    public function connect(array $params): Connection
    {
        $connection = $this->createConnection($params);

        return new Connection($connection, $params);
    }

    /*
     * In my testing using plain CURL is nearly twice as fast as using Guzzle. It adds up.
     */
    private function createConnection(array $params): \CurlHandle|false
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, false);

        if (isset($params['username']) && isset($params['password'])) {
            $username = $params['username'];
            $password = $params['password'];
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        }

        return $ch;
    }

}
