<?php

namespace Wanwire\RQLite;

use Illuminate\Support\ServiceProvider;
use Wanwire\RQLite\Connect\Connection;
use Wanwire\RQLite\Connect\Connector;

class RQLiteProvider extends ServiceProvider
{
    public function register(): void
    {
        Connection::resolverFor('rqlite', function ($connection, $database, $prefix, $config) {
            return new Connection($connection, $database, $prefix, $config);
        });
    }

    public function boot()
    {
        $this->app->bind('db.connector.rqlite', Connector::class);
    }
}
