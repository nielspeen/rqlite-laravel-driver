<?php

namespace Wanwire\LaravelEloquentRQLite;

use Illuminate\Database\Connection;
use Wanwire\LaravelEloquentRQLite\Driver\RQLiteConnection;
use Wanwire\LaravelEloquentRQLite\Driver\RQLiteDriver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelEloquentRQLiteServiceProvider extends PackageServiceProvider
{
    public function register(): void
    {
        Connection::resolverFor('rqlite', function ($connection, $database, $prefix, $config) {
            return new RQLiteConnection($connection, $database, $prefix, $config);
        });
    }

    public function boot()
    {
        $this->app->bind('db.connector.rqlite', function () {
            return new RQLiteDriver();
        });
    }

    public function configurePackage(Package $package): void
    {
        $package->name('laravel-eloquent-rqlite');
    }
}
