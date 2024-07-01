<?php

namespace Wanwire\RQLite\Connect;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\SQLiteConnection;
use Wanwire\RQLite\PDO\PDO;

class Connection extends SQLiteConnection
{
    public function setConsistencyLevel($level): void
    {
        $this->getPdo()->setAttribute(PDO::RQLITE_ATTR_CONSISTENCY, $level);
    }

    public function getConsistencyLevel(): string
    {
        return $this->getPdo()->getAttribute(PDO::RQLITE_ATTR_CONSISTENCY);
    }

}
