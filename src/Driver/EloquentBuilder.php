<?php

namespace Wanwire\RQLite\Driver;

use Illuminate\Database\Eloquent\Builder;
use Wanwire\RQLite\Connect\Connection;
use Wanwire\RQLite\Exceptions\RQLiteDriverException;
use Wanwire\RQLite\PDO\PDO;

class EloquentBuilder extends Builder
{
    protected string $consistencyLevel = 'strong';
    protected ?int $freshness = null;
    protected ?int $strictFreshness = null;

    public function addConsistencyLevel($level): static
    {
        $this->consistencyLevel = $level;

        return $this;
    }

    public function addFreshness(?int $seconds): static
    {
        if($this->strictFreshness) {
            throw new RQLiteDriverException('You cannot set both freshness and strict freshness at the same time');
        }

        $this->freshness = $seconds;

        return $this;
    }

    public function addStrictFreshness(?int $seconds): static
    {
        if($this->freshness) {
            throw new RQLiteDriverException('You cannot set both freshness and strict freshness at the same time');
        }

        $this->strictFreshness = $seconds;

        return $this;
    }

    protected function applyParameters(): void
    {
        if ($this->query->connection instanceof Connection) {
            $this->query->connection->getPdo()->setAttribute(PDO::RQLITE_ATTR_CONSISTENCY, $this->consistencyLevel);
            $this->query->connection->getPdo()->setAttribute(PDO::RQLITE_ATTR_FRESHNESS, $this->freshness);
            $this->query->connection->getPdo()->setAttribute(PDO::RQLITE_ATTR_FRESHNESS_STRICT, $this->strictFreshness);
        }
    }

    protected function resetParameters(): void
    {
        if ($this->query->connection instanceof Connection) {
            $this->query->connection->getPdo()->setAttribute(PDO::RQLITE_ATTR_CONSISTENCY, 'strong');
            $this->query->connection->getPdo()->setAttribute(PDO::RQLITE_ATTR_FRESHNESS, null);
            $this->query->connection->getPdo()->setAttribute(PDO::RQLITE_ATTR_FRESHNESS_STRICT, null);
        }
    }

    public function get($columns = ['*'])
    {
        return $this->runQueryWithConsistencyLevel($columns, [], function ($columns) {
            return parent::get($columns);
        });
    }

    public function count($columns = '*')
    {
        return $this->runQueryWithConsistencyLevel($columns, [], function ($columns) {
            return parent::count($columns);
        });
    }

    public function sum($column)
    {
        return $this->runQueryWithConsistencyLevel($column, [], function ($column) {
            return parent::sum($column);
        });
    }


    protected function runQueryWithConsistencyLevel($query, $bindings, \Closure $callback)
    {
        $this->applyParameters();
        $result = $callback($query, $bindings);
        $this->resetParameters();

        return $result;
    }


}
