<?php

namespace Wanwire\RQLite\Driver;

use Illuminate\Database\Eloquent\Builder;
use Wanwire\RQLite\Connect\Connection;
use Wanwire\RQLite\PDO\PDO;

class EloquentBuilder extends Builder
{
    protected string $consistencyLevel = 'strong';

    protected function newBaseQueryBuilder(): \Illuminate\Database\Query\Builder
    {
        $connection = $this->getConnection();

        return new \Illuminate\Database\Query\Builder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }

    public function addConsistencyLevel($level): static
    {
        $this->consistencyLevel = $level;

        return $this;
    }

    protected function applyConsistencyLevel(): void
    {
        if ($this->query->connection instanceof Connection) {
            $this->query->connection->getPdo()->setAttribute(PDO::RQLITE_ATTR_CONSISTENCY, $this->consistencyLevel);
        }
    }

    protected function resetConsistencyLevel(): void
    {
        if ($this->query->connection instanceof Connection) {
            $this->query->connection->getPdo()->setAttribute(PDO::RQLITE_ATTR_CONSISTENCY, 'strong');
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
        $this->applyConsistencyLevel();
        $result = $callback($query, $bindings);
        $this->resetConsistencyLevel();

        return $result;
    }


}
