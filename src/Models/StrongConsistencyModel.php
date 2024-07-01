<?php

namespace Wanwire\RQLite\Models;

use Illuminate\Database\Eloquent\Model;
use Wanwire\RQLite\PDO\PDO;
use Wanwire\RQLite\Traits\WithRQLiteBuilder;

class StrongConsistencyModel extends Model
{
    use WithRQLiteBuilder;

    protected string $consistencyLevel = PDO::RQLITE_CONSISTENCY_STRONG;
}
