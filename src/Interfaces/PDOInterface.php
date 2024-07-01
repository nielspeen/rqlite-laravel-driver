<?php

namespace Wanwire\RQLite\Interfaces;

use PDO;

interface PDOInterface
{
    public function beginTransaction(): bool;
    public function commit(): bool;
    // TODO: Uncomment the following lines and implement the methods
    //    public function errorCode();
    //    public function errorInfo();
    public function getAttribute(int $attribute): mixed;
    public function inTransaction(): bool;
    public function lastInsertId(?string $name = null): string;
    public function prepare(string $query, array $options = []);
    public function quote(string $string, $type = PDO::PARAM_STR): string|false;
    public function rollback(): bool;
    public function setAttribute($attribute, $value): bool;
}
