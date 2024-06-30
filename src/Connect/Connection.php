<?php

namespace Wanwire\RQLite\Connect;

use Illuminate\Database\SQLiteConnection;

class Connection extends SQLiteConnection
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
