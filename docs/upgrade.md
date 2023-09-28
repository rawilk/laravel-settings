---
title: Upgrade Guide
sort: 4
---

# Upgrading from v2 to v3

> {info} I've attempted to document every possible breaking change, however there may be some issues I've missed.
> If you run into a breaking change not documented here, please submit a PR to the docs to update it.

## Updating Dependencies

### Laravel 10.0 required

Laravel 10.0 is now required. If you are still using Laravel 8.0 or 9.0, you will need to upgrade to Laravel 10.0 before upgrading to v3.

### PHP 8.1 required

`laravel-settings` now requires PHP 8.1.0 or greater.

## Updating Configuration

### Config file changes

The following configuration options should be added to your `config/settings.php` file if you have it published:

```php
    /*
    |--------------------------------------------------------------------------
    | Teams
    |--------------------------------------------------------------------------
    |
    | When set to true the package implements teams using the `team_foreign_key`.
    |
    | If you want the migrations to register the `team_foreign_key`, you must
    | set this to true before running the migration.
    |
    | If you already ran the migrations, then you must make a new migration to
    | add the `team_foreign_key` column to the settings table, and update the
    | unique constraint on the table. See the `add_settings_team_field` migration
    | for how to do this.
    |
    */
    'teams' => false,

    /*
    |--------------------------------------------------------------------------
    | Team Foreign Key
    |--------------------------------------------------------------------------
    |
    | When teams is set to true, our database/eloquent drivers will use this
    | column as a team foreign key to scope queries to.
    |
    | The team id will also be included in a cache key when caching is enabled.
    |
    */
    'team_foreign_key' => 'team_id',

    /*
    |--------------------------------------------------------------------------
    | Context Serializer
    |--------------------------------------------------------------------------
    |
    | The context serializer is responsible for converting a Context object
    | into a string, which gets appended to a setting key in the database.
    |
    | Any custom serializer you use must implement the
    | \Rawilk\Settings\Contracts\ContextSerializer interface.
    |
    | Supported:
    | - \Rawilk\Settings\Support\ContextSerializers\ContextSerializer (default)
    | - \Rawilk\Settings\Support\ContextSerializers\DotNotationContextSerializer
    |
    */
    'context_serializer' => \Rawilk\Settings\Support\ContextSerializers\ContextSerializer::class,

    /*
    |--------------------------------------------------------------------------
    | Key Generator
    |--------------------------------------------------------------------------
    |
    | The key generator is responsible for generating a suitable key for a
    | setting.
    |
    | Any custom key generator you use must implement the
    | \Rawilk\Settings\Contracts\KeyGenerator interface.
    |
    | Supported:
    | - \Rawilk\Settings\Support\KeyGenerators\ReadableKeyGenerator
    | - \Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator (default)
    |
    */
    'key_generator' => \Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator::class,

    /*
    |--------------------------------------------------------------------------
    | Value Serializer
    |--------------------------------------------------------------------------
    |
    | By default, we use php's serialize() and unserialize() functions to
    | prepare the setting values for storage. You may use the `JsonValueSerializer`
    | instead if you want to store the values as json instead.
    |
    | Any custom value serializer you use must implement the
    | \Rawilk\Settings\Contracts\ValueSerializer interface.
    |
    */
    'value_serializer' => \Rawilk\Settings\Support\ValueSerializers\ValueSerializer::class,
```

### Migrations

With teams being supported in v3, a new migration has been added to add a `team_id` column to the settings table. If you are using
the database or eloquent drivers and plan on using teams, be sure to set the `teams` configuration option to `true`, and then publish
and run the new migration from the package:

```bash
php artisan vendor:publish --tag="settings-migrations"
php artisan migrate
```

## Contracts

Some of the interfaces have changed, so if you are using custom drivers or extending any of ours, be sure to update your code to be compatible with the
updated interfaces.

### Driver Contract

All the method signatures in the `Driver` interface have changed to accept a `$teamId = null` argument. There are also new methods added for `all()` and `flush()`. Here's
what the interface looks like now:

```php
<?php

declare(strict_types=1);

namespace Rawilk\Settings\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface Driver
{
    public function forget($key, $teamId = null);

    public function get(string $key, $default = null, $teamId = null);

    public function all($teamId = null, $keys = null): array|Arrayable;

    public function has($key, $teamId = null): bool;

    public function set(string $key, $value = null, $teamId = null);

    public function flush($teamId = null, $keys = null);
}
```

### Setting Model Contract

All method signatures in the `Setting` contract have changed to accept a `$teamId = null` argument.
New methods for `getAll()` and `flush()` have been added as well. Here's what the interface looks like now:

```php
<?php

declare(strict_types=1);

namespace Rawilk\Settings\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface Setting
{
    public static function getValue(string $key, $default = null, $teamId = null);

    public static function getAll($teamId = null, $keys = null): array|Arrayable;

    public static function has($key, $teamId = null): bool;

    public static function removeSetting($key, $teamId = null);

    public static function set(string $key, $value = null, $teamId = null);

    public static function flush($teamId = null, $keys = null);
}
```

## HasSettings Model Trait

A `deleted` model event observer has been added to the trait to flush any model settings when the model is deleted. In most cases,
you shouldn't need to do anything, however you may disable this behavior by setting a `$flushSettingsOnDelete` static property
on the model to `false`.

## Setting Model

The `Setting` model has been updated to support teams and conform with the updated `Setting` interface. If you are extending
this model, you may need to update your code to be compatible with the new model.

## Setting Service

Several changes have been made to the underlying `Rawilk\Settings\Settings` service class. For most people there shouldn't be any action required,
however if you are adding macros to the class, you may need to tweak them to be compatible with updated class.
