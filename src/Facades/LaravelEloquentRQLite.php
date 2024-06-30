<?php

namespace Wanwire\LaravelEloquentRQLite\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelEloquentRQLite extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wanwire\LaravelEloquentRQLite\LaravelEloquentRQLite::class;
    }
}
