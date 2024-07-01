<?php

namespace Wanwire\RQLite\Models;

use Illuminate\Database\Eloquent\Model;
use Wanwire\RQLite\PDO\PDO;
use Wanwire\RQLite\Traits\WithRQLiteBuilder;

class NoConsistencyModel extends Model
{
    use WithRQLiteBuilder;

    protected string $consistencyLevel = PDO::RQLITE_CONSISTENCY_NONE;
}
