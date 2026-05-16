---
title: Upgrade Guide
sort: 4
---

# Upgrading from v3 to v4

> {info} I've attempted to document every possible breaking change, however there may be some issues I've missed.
> If you run into a breaking change not documented here, please submit a PR to the docs to update it.

## Updating Dependencies

### Laravel 12.0 required

Laravel 12.0 is now required. If you are using an earlier version of Laravel, you will need to upgrade to Laravel 12.0 before upgrading to v4.

### PHP 8.2 required

`laravel-settings` now requires PHP 8.2 or greater.

## Updating Configuration

### Config file additions

The following configuration options should be added to your `config/settings.php` file if you have it published:

```php
    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | By default, we will cache settings forever. Set a ttl value here to limit
    | how long the cached setting is valid. We will use Cache::flex() if you
    | set an array ttl value here.
    |
    */
    'cache_ttl' => null,
```

### Config file changes

Several changes have been made to the default configuration in the `config/settings.php` file. If you haven't published the configuration file, some of these changes will break your settings implementation.

- `context_serializer` is now defaulted to the `KeyValueContextSerializer` instead of the `ContextSerializer` class.
- `key_generator` is now defaulted to the `ReadableKeyGenerator` instead of the `Md5KeyGenerator` class.
- `value_serializer` is now defaulted to the `JsonValueSerializer` instead of the `ValueSerializer` class.

## Contracts

Some interfaces have changed, so if you are using custom drivers or extending any of ours, be sure to update your code to be compatible with the
updated interfaces.

### Driver Contract

The `get()` method signature has changed to remove the `$default` parameter. The signature is now:

```php
public function get(string $key, $teamId = null);
```

### Setting Model Contract

The `getValue()` method signature has changed to remove the `$default` parameter. The signature is now:

```php
public static function getValue(string $key, $teamId = null);
```

## Setting Model

The `Setting` model has dropped the `$teamForeignKey` property, so if you are extending the model and reference that property, it is now gone. You can use `SettingsConfig::getTeamsForeignKey()` to get the current team foreign key instead.

## Setting Service

Several changes have been made to the underlying `Rawilk\Settings\Settings` service class. For most people there shouldn't be any action required,
however, if you are adding macros to the class, you may need to tweak them to be compatible with the updated class.

### Setting Service Class Binding

In previous versions of the package, we bound the Settings service class as a singleton to the container. In v4, however, we are just using a regular binding, so the service is no longer a singleton. This change allowed us to resolve some issues and pain points with the settings service in previous versions of the package. Most applications shouldn't be affected by this change, however, you should be sure to test your implementation to verify everything is working as expected.

## DotNotationContextSerializer

This context serializer has been renamed to `KeyValueContextSerializer` in v4. You will need to update any references to the old name in your application and config files.
