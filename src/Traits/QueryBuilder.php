<?php

namespace Wanwire\RQLite\Traits;

use Wanwire\RQLite\Driver\EloquentBuilder;

trait QueryBuilder
{

    public function newEloquentBuilder($query): EloquentBuilder
    {
        $builder = new EloquentBuilder($query);

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
