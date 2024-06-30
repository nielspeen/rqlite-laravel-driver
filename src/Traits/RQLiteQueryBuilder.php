<?php

namespace Wanwire\LaravelEloquentRQLite\Traits;

use Wanwire\LaravelEloquentRQLite\Driver\RQLiteEloquentBuilder;

trait RQLiteQueryBuilder
{

    public function newEloquentBuilder($query): RQLiteEloquentBuilder
    {
        $builder = new RQLiteEloquentBuilder($query);

        if(isset($this->consistencyLevel)) {
            $builder->addConsistencyLevel($this->consistencyLevel);
        }

        return $builder;
    }

    public function scopeNoConsistency($query)
    {
        return $query->withConsistencyLevel('none');
    }

    public function scopeWeakConsistency($query)
    {
        return $query->withConsistencyLevel('weak');
    }

    public function scopeStrongConsistency($query)
    {
        return $query->withConsistencyLevel('strong');
    }

    public function scopeWithConsistencyLevel($query, $level)
    {
        return $query->addConsistencyLevel($level);
    }
}
