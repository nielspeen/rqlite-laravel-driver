<?php

namespace Wanwire\RQLite\Connect;

use Illuminate\Database\Connectors\SQLiteConnector;
use Wanwire\RQLite\PDO\PDO;

class Connector extends SQLiteConnector
{
    public function connect(array $config): \PDO|PDO
    {
        $options = $this->getOptions($config);

        $dsn = "rqlite:host={$config['host']};port={$config['port']}";

        return $this->createConnection($dsn, $config, $options);
    }

    public function createConnection($dsn, array $config, array $options): PDO
    {
        return new PDO($dsn, $config['username'], $config['password'], $options);
    }

}
