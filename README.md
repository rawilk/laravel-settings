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

## Usage

``` php
use Rawilk\Settings\Facades\Settings;

// set a new value
Settings::set('foo', 'bar');

// update a value
Settings::set('foo', 'updated value');

// retrieve a value
Settings::get('foo'); // 'updated value'

// retrieve a non-persisted setting
Settings::get('not persisted', 'my default'); // 'my default'

// set and check a boolean value
Settings::set('app.debug', true);
Settings::isTrue('app.debug'); // true
Settings::isFalse('app.debug'); // false

// Check if a setting is persisted
Settings::has('foo'); // true

// Remove a setting from storage
Settings::forget('foo');
```

## Contextual Settings
If you need settings based on context, let's say for a specific user, you can do that easily as well, using the `\Rawilk\Settings\Support\Context` class.

```php
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Facades\Settings;

// You can put anything you want in the context, as long it is in array form.
$userContext = new Context(['user_id' => 1]);
$user2Context = new Context(['user_id' => 2]);
Settings::context($userContext)->set('notifications', true);
Settings::context($user2Context)->set('notifications', false);

Settings::context($userContext)->isTrue('notifications'); // true
Settings::context($user2Context)->isTrue('notifications'); // false
```

## Settings Helper
If you prefer to use a helper function, you can use the `settings()` helper function. If you pass nothing in to the function, it will return an instance of the `\Rawilk\Settings\Settings` class, which you can then call any of its methods on as if you were using the `Settings` facade.

Passing a key in as your first argument will return a persisted setting value for that key. You can pass a default value in as the second argument and that will be returned if the setting is not persisted. If you need context, you can pass that in as the third argument.

You can pass an array of key/value pairs as the first argument to set or update setting values. If you need context, you can pass it in as the third argument to the helper function (pass in `null` as the second argument as this argument is ignored anyways in this case). 

## Extending
You can easily extend settings to use your own drivers for storing and retrieving settings, such as using a json or xml file. To do so, you will need to add your driver's configuration in the `drivers` key in the `settings` config file, with the following minimum configuration:

```php
'drivers' => [
    // ... other drivers
    'custom' => [
        'driver' => 'custom',
        // driver specific configuration
    ],
],
```

You will then need to tell settings about your driver in a service provider:

```php
// The callback function is used to create your custom driver, and will receive 
// an application instance and an array of your driver's configuration.
app('SettingsFactory')->extend('custom', fn ($app, $config) => new CustomDriver($config));

// You can also set this driver as the default driver here, or in the config file.
app('SettingsFactory')->setDefaultDriver('custom');
```

> **Note:** Your custom driver must implement the `\Rawilk\Settings\Contracts\Driver` interface.

## Extending Settings
`\Rawilk\Settings\Settings` is Macroable, so you can add any custom functionality you want to the class. The best place to do so would be in a service provider.

```php
use Rawilk\Settings\Settings;

Settings::macro('getWithSuffix', function ($key, $suffix) {
    // Inside this closure you can call any method available on `Settings`.
    $value = $this->get($key);

    return $value . '_' . $suffix;
});
```

```php
use Rawilk\Settings\Facades\Settings;

Settings::set('foo', 'bar');

Settings::getWithSuffix('foo', 'some_suffix'); // 'bar_some_suffix'
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email randall@randallwilk.dev instead of using the issue tracker.

## Credits

- [Randall Wilk](https://github.com/rawilk)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
