# Laravel driver for Rqlite

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require nielspeen/laravel-eloquent-rqlite
```

lumen framework add below to bootstrap/app.php
```php
$app->register(Wanwire\LaravelEloquentRqlite\LaravelEloquentRqliteServiceProvider::class);
```

lumen framework add config to config/database.php
```php 
'connections' => [
        
        'rqlite' => [
            'driver' => 'rqlite',
            'database' => env('DB_DATABASE', ':memory:'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '4001'),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => env('DB_PREFIX', ''),
        ],
        // ...
   ]
```


## Credits

- [hushulin](https://github.com/hushulin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
