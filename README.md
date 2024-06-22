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
            'read' => [
                'driver' => 'sqlite',
                'url' => env('DATABASE_URL'),
                'database' => env('DB_RQLITE_DATABASE', '/var/lib/rqlite/db.sqlite'),
                'prefix' => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),

            ],
            'write' => [
                'driver' => env('DB_RQLITE_CONNECTION', 'rqlite'),
                'database' => env('DB_RQLITE_DATABASE', ':memory:'),
                'host' => env('DB_RQLITE_HOST', '127.0.0.1'),
                'port' => env('DB_RQLITE_PORT', '4001'),
                'username' => env('DB_RQLITE_USERNAME', ''),
                'password' => env('DB_RQLITE_PASSWORD', ''),
            ],
            'sticky' => false,
        ],
        // ...
   ]
```

When we want a more consistent read, and don't want to use the sticky option, we can do something like:

```
Model::onWriteConnection()->find(1);
```

## Credits

- [nielspeen][https://github.com/nielspeen)
- [hushulin](https://github.com/hushulin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
