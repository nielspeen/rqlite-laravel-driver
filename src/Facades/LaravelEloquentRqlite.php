<?php

namespace Wanwire\LaravelEloquentRqlite\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelEloquentRqlite extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wanwire\LaravelEloquentRqlite\LaravelEloquentRQLite::class;
    }
}
