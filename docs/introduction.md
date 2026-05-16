---
title: Introduction
sort: 1
---

Settings for Laravel enables you to store your application's settings in the database. It can work alongside the built-in
configuration system that Laravel offers. With this package, you can store application-specific settings that wouldn't
make sense to store in a configuration file, or that you want end-users to be able to update through your application's
UI.

The package also offers caching on a per-setting basis out of the box, so no unnecessary extra queries are
performed when retrieving settings. The caching works no matter which driver you choose to use. The
package also encrypts your settings automatically for you if you need to store sensitive data such as
passwords for a third-party service you need to be able to use later.

To get and retrieve stored settings, you can do it with the `Settings` facade or by using the `settings()` helper
function:

```php
use Rawilk\Settings\Facades\Settings;

// Store
Settings::set('foo', 'bar');
settings()->set('foo', 'bar');
settings(['foo' => 'bar']);

// Retrieving
Settings::get('foo'); // 'bar
settings()->get('foo');
settings('foo');
```

## Alternatives

-   [spatie/laravel-settings](https://github.com/spatie/laravel-settings)

## Disclaimer

This package is not affiliated with, maintained, authorized, endorsed or sponsored by Laravel or any of its affiliates.

The package only supports the stable versions of PHP and Laravel that are tested against in the automated test suite.
