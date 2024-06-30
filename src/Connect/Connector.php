<?php

namespace Wanwire\RQLite\Connect;

use Illuminate\Database\Connectors\SQLiteConnector;
use JetBrains\PhpStorm\NoReturn;
use Wanwire\RQLite\PDO\PDO;

class Connector extends SQLiteConnector
{

    #[NoReturn] public function connect(array $config)
    {
        $options = $this->getOptions($config);

        $dsn = "rqlite:host={$config['host']};port={$config['port']}";

        return $this->createConnection($dsn, $config, $options);
    }

    /*
     * In my testing plain CURL is nearly twice as fast as Guzzle. It adds up.
     */
    public function createConnection($dsn, array $config, array $options)
    {


        return new PDO($dsn, $config['username'], $config['password'], $options);
    }

}
