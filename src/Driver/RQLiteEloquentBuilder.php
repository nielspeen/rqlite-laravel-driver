<?php

namespace Wanwire\LaravelEloquentRqlite\Driver;

use Illuminate\Database\Eloquent\Builder;

class RQLiteEloquentBuilder extends Builder
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
        if ($this->query->connection instanceof RQLiteConnection) {
            $this->query->connection->setConsistencyLevel($this->consistencyLevel);
        }
    }

    protected function resetConsistencyLevel(): void
    {
        if ($this->query->connection instanceof RQLiteConnection) {
            $this->query->connection->setConsistencyLevel('strong');
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
