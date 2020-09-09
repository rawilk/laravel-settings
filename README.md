# Laravel Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rawilk/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rawilk/laravel-settings)
![Tests](https://github.com/rawilk/laravel-settings/workflows/Tests/badge.svg?style=flat-square)
[![Total Downloads](https://img.shields.io/packagist/dt/rawilk/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rawilk/laravel-settings)

Laravel Settings allows you to store your application settings in the database. It works alongside of the built-in configuration system that Laravel offers. With this package, you can store application specific settings that wouldn't make sense to store in a configuration file, or that you want end-users to be able to update through your application's UI.

The package also offers caching on a per-setting basis out of the box, so no unnecessary extra queries are performed once a setting has been retrieved. The caching works no matter which driver you choose to use. The package can also encrypt your settings automatically for you as well if you need to store sensitive data such as passwords for a third-party service you need to be able to use later.

To get and retrieve stored settings, you can do it easily with the Settings Facade or by using the `settings()` helper function:

```php
// Setting
Settings::set('foo', 'bar');
settings()->set('foo', 'bar');
settings(['foo' => 'bar']);

// Retrieving
Settings::get('foo'); // 'bar'
settings()->get('foo');
settings('foo');
```

## Documentation
For documentation, please visit: https://randallwilk.dev/docs/laravel-settings
## Installation

You can install the package via composer:

```bash
composer require rawilk/laravel-settings
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Rawilk\Settings\SettingsServiceProvider" --tag="migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Rawilk\Settings\SettingsServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Settings Table
    |--------------------------------------------------------------------------
    |
    | Database table used to store settings in.
    |
    */
    'table' => 'settings',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | If enabled, all settings are cached after accessing them.
    |
    */
    'cache' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | Specify a prefix to prepend to any setting key being cached.
    |
    */
    'cache_key_prefix' => 'settings.',

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    |
    | If enabled, all values are encrypted and decrypted.
    |
    */
    'encryption' => true,

    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    |
    | The driver to use to store and retrieve settings from. You are free
    | to add more drivers in the `drivers` configuration below.
    |
    */
    'driver' => env('SETTINGS_DRIVER', 'eloquent'),

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the driver information for each repository that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with this package. You are free to add more.
    |
    | Each driver you add must implement the \Rawilk\Settings\Contracts\Driver interface.
    |
    */
    'drivers' => [
        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CONNECTION', 'mysql'),
        ],
        'eloquent' => [
            'driver' => 'eloquent',

            /*
             * You can use any model you like for the setting, but it needs to implement
             * the \Rawilk\Settings\Contracts\Setting interface.
             */
            'model' => \Rawilk\Settings\Models\Setting::class,
        ],
    ],
];
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email randall@randallwilk.dev instead of using the issue tracker.

## Credits

- [Randall Wilk](https://github.com/rawilk)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
