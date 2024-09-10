<?php

namespace Wanwire\RQLite\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Wanwire\RQLite\Connect\Connection;
use Wanwire\RQLite\Exceptions\RQLiteDriverException;
use Wanwire\RQLite\PDO\PDO;

class Builder extends BaseBuilder
{
    protected string $consistencyLevel = 'strong';
    protected bool $queuedWrites = false;
    protected ?int $freshness = null;
    protected ?int $strictFreshness = null;

    public function addConsistencyLevel($level): static
    {
        $this->consistencyLevel = $level;
        return $this;
    }

    public function addFreshness(?int $seconds): static
    {
        if ($this->strictFreshness) {
            throw new RQLiteDriverException('You cannot set both freshness and strict freshness at the same time');
        }
        $this->freshness = $seconds;
        return $this;
    }

    public function addStrictFreshness(?int $seconds): static
    {
        if ($this->freshness) {
            throw new RQLiteDriverException('You cannot set both freshness and strict freshness at the same time');
        }
        $this->strictFreshness = $seconds;
        return $this;
    }

    public function addQueuedWrites(): static
    {
        $this->queuedWrites = true;
        return $this;
    }


    protected function applyParameters(): void
    {
        if ($this->query->connection instanceof Connection) {
            $pdo = $this->query->getConnection()->getPdo();
            $pdo->setAttribute(PDO::RQLITE_ATTR_CONSISTENCY, $this->consistencyLevel);
            $pdo->setAttribute(PDO::RQLITE_ATTR_FRESHNESS, $this->freshness);
            $pdo->setAttribute(PDO::RQLITE_ATTR_FRESHNESS_STRICT, $this->strictFreshness);
            $pdo->setAttribute(PDO::RQLITE_ATTR_QUEUED_WRITES, $this->queuedWrites);
        }
    }

    protected function resetParameters(): void
    {
        if ($this->query->connection instanceof Connection) {
            $pdo = $this->query->getConnection()->getPdo();
            $pdo->setAttribute(PDO::RQLITE_ATTR_CONSISTENCY, 'strong');
            $pdo->setAttribute(PDO::RQLITE_ATTR_FRESHNESS, null);
            $pdo->setAttribute(PDO::RQLITE_ATTR_FRESHNESS_STRICT, null);
        }
    }

    public function get($columns = ['*'])
    {
        return $this->runQueryWithParameters($columns, function ($columns) {
            $maxRetries = 3;
            $retryDelay = 100; // milliseconds

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    return parent::get($columns);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->getCode() == 8 && $attempt < $maxRetries) {
                        usleep($retryDelay * 1000);
                        $retryDelay *= 2; // Exponential backoff
                        continue;
                    }
                    throw $e;
                }
            }
        });
    }

    protected function runQueryWithParameters($columns, \Closure $callback)
    {
        $this->applyParameters();
        $result = $callback($columns);
        // Disabled this for now, because I have not found any other way to ensure eager loading follows the set
        // consistency level unless we just don't touch it.
        // $this->resetParameters();
        return $result;
    }

    public function toBase(): \Illuminate\Database\Query\Builder
    {
        $this->applyParameters();
        return $this->applyScopes()->getQuery();
    }

}
