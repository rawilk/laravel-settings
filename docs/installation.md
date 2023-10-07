---
title: Installation
sort: 3
---

## Installation

laravel-settings can be installed via composer:

```bash
composer require rawilk/laravel-settings
```

## Migrations

When using the `database` or `eloquent` drivers, you should publish the migration files. You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="settings-migrations"
php artisan migrate
```

> {note} If you plan on using the [teams](/docs/laravel-settings/{version}/basic-usage/teams) feature, you need to publish the config first
> and enable the `teams` option before running the migrations.

## Configuration

You can publish the configuration file with:

```bash
php artisan vendor:publish --tag="settings-config"
```

You can view the default configuration here: https://github.com/rawilk/laravel-settings/blob/{branch}/config/settings.php

### Generators

For backwards compatibility and to reduce the amount of breaking changes from v2, the package uses the `Md5KeyGenerator` class by default. This key generator generates
a md5 hash of a serialized setting key and context object combination. Using this generator, however, prevents you from using some new features, such as `all` and `flush`
on the settings facade, as well as flushing a model's settings when it is deleted.

To use these features, you must use the new `ReadableKeyGenerator` class or a custom key generator class of your own. This key generator will not hash the setting key in any way,
allowing the package to search for settings easier by key, and partial searches by context. To use this key generator, you just need to update the settings config file:

```php
// config/settings.php
'key_generator' => \Rawilk\Settings\Support\KeyGenerators\ReadableKeyGenerator::class,
```

For more information on the key generators, see the [Custom Generators](/docs/laravel-settings/{version}/advanced-usage/custom-generators) documentation.

### Default Values

When caching is enabled, and you are attempting to retrieve a setting that is not persisted yet, the settings service will cache the default value provided to `get()`.
This means that any subsequent calls to `get()` for that setting will return the original default value provided to it until the setting is persisted.

This may not always be desirable, but this functionality can easily be disabled in the configuration file:

```php
// config/settings.php
'cache_default_value' => false,
```

With that value set to `false`, the following code will work as expected for retrieving a setting that hasn't been persisted yet:

```php
settings()->get('site.lang', 'en'); // 'en'

// somewhere else in the codebase

settings()->get('site.lang', 'es'); // 'es'
```

For more information on retrieving a value, see [Retrieving a value](/docs/laravel-settings/{version}/basic-usage/basic-usage#user-content-retrieving-a-value) in the docs.

> {tip} This configuration value is set to `true` by default, however in future major versions of this package, it may be defaulted to `false`.
