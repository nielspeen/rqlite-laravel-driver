<?php

namespace Wanwire\RQLite\Traits;

use Wanwire\RQLite\Driver\EloquentBuilder;
use Wanwire\RQLite\PDO\PDO;

trait WithRQLiteBuilder
{
    public function newEloquentBuilder($query): EloquentBuilder
    {
        $builder = new EloquentBuilder($query);

        if ($this->consistencyLevel) {
            $query->addConsistencyLevel($this->consistencyLevel);
        }

        return $builder;
    }

    public function scopeNoConsistency($query)
    {
        return $query->addConsistencyLevel(PDO::RQLITE_CONSISTENCY_NONE);
    }

    public function scopeWeakConsistency($query)
    {
        return $query->addConsistencyLevel(PDO::RQLITE_CONSISTENCY_NONE);
    }

    public function scopeStrongConsistency($query)
    {
        return $query->addConsistencyLevel(PDO::RQLITE_CONSISTENCY_STRONG);
    }

    public function scopeFreshness($query, ?int $seconds = null)
    {
        return $query->addFreshness($seconds);
    }

    public function scopeStrictFreshness($query, ?int $seconds = null)
    {
        return $query->addStrictFreshness($seconds);
    }

    public function scopeWithConsistencyLevel($query, $level)
    {
        return $query->addConsistencyLevel($level);
    }
}
