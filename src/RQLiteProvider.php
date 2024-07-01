<?php

namespace Wanwire\RQLite;

use Illuminate\Support\ServiceProvider;
use Wanwire\RQLite\Connect\Connection;
use Wanwire\RQLite\Connect\Connector;
use Illuminate\Database\Eloquent\Builder;
use Wanwire\RQLite\Driver\EloquentBuilder;

class RQLiteProvider extends ServiceProvider
{
    public function register(): void
    {
        Connection::resolverFor('rqlite', function ($connection, $database, $prefix, $config) {
            return new Connection($connection, $database, $prefix, $config);
        });

        $this->app->bind(Builder::class, EloquentBuilder::class);

    }

    public function boot(): void
    {
        $this->app->bind('db.connector.rqlite', Connector::class);
    }
}
