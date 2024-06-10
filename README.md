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

- [nielspeen][https://github.com/nielspeen)
- [hushulin](https://github.com/hushulin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
