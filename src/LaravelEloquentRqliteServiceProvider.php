<?php

namespace Wanwire\LaravelEloquentRqlite;

use Illuminate\Database\Connection;
use Wanwire\LaravelEloquentRqlite\Driver\RqliteDriver;
use Illuminate\Database\SQLiteConnection;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelEloquentRqliteServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        $this->app->bind('db.connector.rqlite', function () {
            return new RqliteDriver();
        });

        $this->app->resolving('db', function ($db) {
            $db->extend('rqlite', function ($config, $name) {

                $connector = $this->app['db.connector.rqlite'];
                if(isset($config['write'])) {
                    $connection = $connector->connect($config['write']);
                } else {
                    $connection = $connector->connect($config);
                }

                return new SQLiteConnection($connection, ':memory:', '', $config);
            });
        });
    }

    public function configurePackage(Package $package): void
    {
        $package->name('laravel-eloquent-rqlite');
    }
}
