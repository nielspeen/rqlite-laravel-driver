<?php

namespace Wanwire\LaravelEloquentRqlite;

use Illuminate\Database\Connection;
use Wanwire\LaravelEloquentRqlite\Driver\RqliteDriver;
use Illuminate\Database\SQLiteConnection;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelEloquentRqliteServiceProvider extends PackageServiceProvider
{
    public function register(): void
    {
        Connection::resolverFor('rqlite', function ($connection, $database, $prefix, $config) {
            return new SQLiteConnection($connection, $database, $prefix, $config);
        });
    }

    public function boot()
    {
        $this->app->bind('db.connector.rqlite', function () {
            return new RqliteDriver();
        });
    }

    public function configurePackage(Package $package): void
    {
        $package->name('laravel-eloquent-rqlite');
    }
}
