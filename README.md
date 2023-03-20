# Settings for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rawilk/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rawilk/laravel-settings)
![Tests](https://github.com/rawilk/laravel-settings/workflows/Tests/badge.svg?style=flat-square)
[![Total Downloads](https://img.shields.io/packagist/dt/rawilk/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rawilk/laravel-settings)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/rawilk/laravel-settings?style=flat-square)](https://packagist.org/packages/rawilk/laravel-settings)
[![License](https://img.shields.io/github/license/rawilk/laravel-settings?style=flat-square)](https://github.com/rawilk/laravel-settings/blob/main/LICENSE.md)

![social image](https://banners.beyondco.de/Settings%20for%20Laravel.png?theme=light&packageManager=composer+require&packageName=rawilk%2Flaravel-settings&pattern=architect&style=style_1&description=Store+Laravel+application+settings+in+the+database.&md=1&showWatermark=0&fontSize=100px&images=cog)

Settings for Laravel allows you to store your application settings in the database. It works alongside of the built-in configuration system that Laravel offers. With this package, you can store application specific settings that wouldn't make sense to store in a configuration file, or that you want end-users to be able to update through your application's UI.

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
php artisan vendor:publish --tag="settings-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="settings-config"
```

You can view the default configuration here: https://github.com/rawilk/laravel-settings/blob/main/config/settings.php

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email randall@randallwilk.dev instead of using the issue tracker.

## Credits

-   [Randall Wilk](https://github.com/rawilk)
-   [All Contributors](../../contributors)

## Disclaimer

This package is not affiliated with, maintained, authorized, endorsed or sponsored by Laravel or any of its affiliates.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
