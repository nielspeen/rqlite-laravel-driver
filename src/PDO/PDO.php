<?php

namespace Wanwire\RQLite\PDO;

use PDO as BasePDO;
use PDOException;
use ReturnTypeWillChange;
use Wanwire\RQLite\Interfaces\PDOInterface;

class PDO extends BasePDO implements PDOInterface
{
    private const DEFAULT_HOST = '127.0.0.1';
    private const DEFAULT_PORT = '4001';

    public const RQLITE_ATTR_CONSISTENCY = 1;
    public const RQLITE_ATTR_FRESHNESS = 2;
    public const RQLITE_ATTR_FRESHNESS_STRICT = 3;
    public const RQLITE_ATTR_QUEUED_WRITES = 4;

    public const RQLITE_CONSISTENCY_NONE = 'none';
    public const RQLITE_CONSISTENCY_WEAK = 'weak';
    public const RQLITE_CONSISTENCY_STRONG = 'strong';


    private const DSN_REGEX = '/^rqlite:(?:host=([^;]*))?(?:;port=([^;]*))?(?:;username=([^;]*))?(?:;password=([^;]*))?;?(sqlite:.*)?$/';

    private \CurlHandle $connection;
    private PDOStatement $lastStatement;
    private string $baseUrl;
    private ?string $sqliteDsn = null;

    private array $attributes = [
        'consistency' => 'strong',
        'freshness' => null,
        'strict_freshness' => null,
        'queued_writes' => false,
    ];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($dsn, $username = null, $passwd = null, $options = [])
    {
        $params = self::parseDSN($dsn);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, false);
        curl_setopt($ch, CURLOPT_TCP_NODELAY, true);
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if (isset($params['username']) && isset($params['password'])) {
            $username = $params['username'];
            $password = $params['password'];
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        }

        $this->connection = $ch;
        $this->baseUrl = "http://{$params['host']}:{$params['port']}";
        if($params['sqlite']) {
            $this->sqliteDsn = $params['sqlite'];
        }
    }

    private static function parseDSN($dsn): array
    {
        $matches = [];

        if (!preg_match(self::DSN_REGEX, $dsn, $matches)) {
            throw new PDOException(sprintf('Invalid DSN %s', $dsn));
        }

        return [
            'host' => !empty($matches[1]) ? $matches[1] : self::DEFAULT_HOST,
            'port' => !empty($matches[2]) ? $matches[2] : self::DEFAULT_PORT,
            'username' => !empty($matches[3]) ? $matches[3] : null,
            'password' => !empty($matches[4]) ? $matches[4] : null,
            'sqlite' => !empty($matches[5]) ? $matches[5] : null,
        ];
    }

    public function beginTransaction(): bool
    {
        throw new PDOException('BEGIN invalid for RQLite.');
    }

    public function commit(): bool
    {
        throw new PDOException('COMMIT invalid for RQLite.');
    }

    public function getAttribute(int $attribute): mixed
    {
        switch ($attribute) {
            case self::RQLITE_ATTR_CONSISTENCY:
                return $this->attributes['consistency'];
        }

        return null;
    }

    public function inTransaction(): bool
    {
        return false;
    }

    public function lastInsertId($name = null): string
    {
        return (string) $this->lastStatement->lastInsertId;
    }


    #[ReturnTypeWillChange] public function prepare(string $query, array $options = [])
    {
        $this->lastStatement = new PDOStatement(
            $query,
            $this->connection,
            $this->baseUrl,
            $this->attributes,
            $this->sqliteDsn
        );
        return $this->lastStatement;
    }

    public function quote(string $string, $type = BasePDO::PARAM_STR): string|false
    {
        $string = str_replace("'", "''", $string);

        return "'".addcslashes($string, "\000\n\r\\\032")."'";
    }

    public function rollBack(): bool
    {
        throw new PDOException('ROLLBACK invalid for RQLite.');
    }

    public function setAttribute($attribute, $value): bool
    {
        switch ($attribute) {
            case self::RQLITE_ATTR_CONSISTENCY:
                $this->attributes['consistency'] = $value;
                break;
            case self::RQLITE_ATTR_FRESHNESS:
                $this->attributes['freshness'] = $value;
                break;
            case self::RQLITE_ATTR_FRESHNESS_STRICT:
                $this->attributes['strict_freshness'] = $value;
                break;
            case self::RQLITE_ATTR_QUEUED_WRITES:
                $this->attributes['queued_writes'] = $value;
                break;
        }

        return true;
    }

}
