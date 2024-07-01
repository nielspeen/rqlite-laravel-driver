# RQLite Driver for Laravel

## Supported

* Eloquent Queries
* Read Consistency: Strong, Weak or None
* Read Freshness & Strict Freshness
* Queued Writes

## Not (yet) Supported

* Bulk Writes
* Bulk Selects
* Transactions

## Installation

Install using composer:

```bash
composer require nielspeen/rqlite-laravel-driver
```

## Setup

Sample ```config/database.php``` configuration:

```php 
'connections' => [
        
        'rqlite' => [
            'url' => env('DB_RQLITE_URL', 'rqlite://127.0.0.1:4001/db'),
        ],

        'rqlite2' => [
            'driver' => env('DB_RQLITE_CONNECTION', 'rqlite'),
            'database' => env('DB_RQLITE_DATABASE', 'db'),
            'host' => env('DB_RQLITE_HOST', '127.0.0.1'),
            'port' => env('DB_RQLITE_PORT', '4001'),
            'username' => env('DB_RQLITE_USERNAME', null),
            'password' => env('DB_RQLITE_PASSWORD', null),
        ],

        // ...
   ]
```

Note that the database **db** name is ignored as RQLite currently supports only a single database. I recommend you specify
a database name anyway, for maximum compatibility with Laravel.

## Usage

By default, all queries are executed with **strong** consistency. You can specify the consistency level by using the 
methods show below.

### Using a Model Trait

```php
use Wanwire\RQLite\PDO\PDO;
use Wanwire\RQLite\WithRQLiteBuilder;

class MyModel extends Model   

{
    use WithRQLiteBuilder;
    protected string $consistency = PDO::RQLITE_CONSISTENCY_STRONG; // or '_WEAK' or '_NONE'
```

### Extended RQLite Models

```php
use \Wanwire\RQLite\Models\WeakConsistencyModel;

class MyModel extends WeakConsistencyModel
```

### Using the query builder:

```php

User::noConsistency()->where('admin', 1)->find(1);
User::weakConsistency()->find(323);
User::strongConsistency()->find(747);
```

### PDO

Sometimes you want to access the PDO object directly. For example:

```php
$pdo = DB::getPdo();

// Set custom PDO attribute
$pdo->setAttribute(Wanwire\RQLite\Pdo\PDO::RQLITE_ATTR_QUEUED_WRITES, true);

// Perform a single query to update both columns
DB::table('users')
    ->where('user_id', 999)
    ->update([
        'bytes_downloaded' => DB::raw("bytes_downloaded + $bytesDownloaded"),
        'bytes_uploaded' => DB::raw("bytes_uploaded + $bytesUploaded"),
    ]);
```

### Warning

The consistency level is not reset after a query is executed. Consecutive queries will use the same consistency level 
unless a new consistency level is set using the Trait, the Models, or the query builder.

This approach ensures that eager loading uses the same consistency level as the primary model being queried.

## Credits

- [nielspeen][https://github.com/nielspeen)
- [hushulin](https://github.com/hushulin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
