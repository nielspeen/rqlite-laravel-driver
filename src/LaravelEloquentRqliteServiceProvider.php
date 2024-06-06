<?php

namespace Wanwire\LaravelEloquentRqlite;

use Wanwire\LaravelEloquentRqlite\Driver\RqliteDriver;
use Illuminate\Database\SQLiteConnection;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelEloquentRqliteServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        $this->app->bind('db.connector.rqlite', function () {
            return new RqliteDriver();
        });

        $this->app->make('db')->resolverFor('rqlite', function ($connection, $database, $prefix, $config) {
            return new SQLiteConnection($connection, $database, $prefix, $config);
        });
    }

    public function configurePackage(Package $package): void
    {
        $package->name('laravel-eloquent-rqlite');
    }
}
