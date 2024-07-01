<?php

namespace Wanwire\RQLite\Traits;

use Wanwire\RQLite\Eloquent\Builder;
use Wanwire\RQLite\PDO\PDO;

trait WithRQLiteBuilder
{
    public function newEloquentBuilder($query): Builder
    {
        $builder = new Builder($query);

        if ($this->consistencyLevel) {
            $builder->addConsistencyLevel($this->consistencyLevel);
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

    public function scopeQueuedWrites($query)
    {
        return $query->addQueuedWrites();
    }
}
