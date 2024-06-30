<?php

namespace Wanwire\RQLite\Interfaces;

use PDO;

interface PDOInterface
{
    public function beginTransaction(): bool;
    public function commit(): bool;
//    public function errorCode();
//    public function errorInfo();
    public function exec($statement);
    public function getAttribute(int $attribute): mixed;
//    public static function getAvailableDrivers();
//    public function getServerVersion();
//    public function getServerInfo();
//    public function inTransaction(): bool;
    public function lastInsertId(?string $name = null): string;
    public function prepare(string $query, array $options = []);
    public function query(?string $query = null, ?int $fetchMode = null, mixed ...$fetchModeArgs);
    public function quote(string $string, $type = PDO::PARAM_STR): string|false;
    public function rollback(): bool;
    public function setAttribute($attribute, $value): bool;
}
