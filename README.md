# RQLite driver for Laravel

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

Note that the database **db** name is ignored as rqlite currently supports only 1 single database. I recommend you specify
a database name anyway, for maximum compatibility with Laravel.

Use the included QueryBuilder trait on models where you want to specify consistency levels.

By default, all queries are executed with **strong** consistency.

You can specify the consistency level by using the following methods.

In your Model:

```php
use Wanwire\RQLite\QueryBuilder as RQLiteQueryBuilder;

class MyModel extends Model   

{
    use RQLiteQueryBuilder;
    protected string $consistency = 'weak'; // or 'strong' or 'none'
```

Using the custom query builder:

```php

User::noConsistency()->find(1);
User::weakConsistency()->find(1);
User::strongConsistency()->find(1);
```

## Credits

- [nielspeen][https://github.com/nielspeen)
- [hushulin](https://github.com/hushulin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
