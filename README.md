# Laravel driver for Rqlite

I modified huhsulin's code to make it work within my Laravel 11 projects. It is by no means complete. 

If you're willing to clean this up and create a fully fledged and properly tested driver, let me know, I can arrange a bounty.

## Installation

You can install the package via composer:

```bash
composer require nielspeen/laravel-eloquent-rqlite
```

lumen framework add below to bootstrap/app.php
```php
$app->register(Wanwire\LaravelEloquentRqlite\LaravelEloquentRqliteServiceProvider::class);
```

We use sqlite for reads, rqlite for writes:
```php 
'connections' => [
        
        'rqlite' => [
            'driver' => env('DB_RQLITE_CONNECTION', 'rqlite'),
            'driver' => env('DB_RQLITE_CONNECTION', 'rqlite'),
            'database' => env('DB_RQLITE_DATABASE', ':memory:'),
            'host' => env('DB_RQLITE_HOST', '127.0.0.1'),
            'port' => env('DB_RQLITE_PORT', '4001'),
            'username' => env('DB_RQLITE_USERNAME', ''),
            'password' => env('DB_RQLITE_PASSWORD', ''),
        ],
        // ...
   ]
```

Use the included RQLiteQueryBuilder trait on models where you want to specify consistency levels.

By default all queries are executed with strong consistency.

You can specify the consistency level by using the following methods:

```php
use Wanwire\LaravelEloquentRqlite\RQLiteQueryBuilder;

protected string $consistency = 'weak'; // or 'strong' or 'none'
```

and override them during queries:

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
