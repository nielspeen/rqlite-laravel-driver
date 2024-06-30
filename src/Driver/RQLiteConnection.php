<?php

namespace Wanwire\LaravelEloquentRqlite\Driver;

use Illuminate\Database\SQLiteConnection;

class RQLiteConnection extends SQLiteConnection
{
    protected string $consistencyLevel = 'strong';

    public function setConsistencyLevel($level): void
    {
        $this->consistencyLevel = $level;
    }

    public function getConsistencyLevel(): string
    {
        return $this->consistencyLevel;
    }

}
