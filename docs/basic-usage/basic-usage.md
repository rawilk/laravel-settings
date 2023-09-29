---
title: Basic Usage
sort: 1
---

## Introduction

You can interact with settings via the `Settings` facade, or by using the `settings()` helper function, which returns an instance of `Rawilk\Settings\Settings`.

## Setting a value

```php
// Create a new setting
Settings::set('foo', 'bar');

// Update an existing setting
Settings::set('foo', 'updated value');
```

## Retrieving a value

```php
Settings::get('foo');

// Retrieve a non-persisted setting
Settings::get('not persisted', 'my default'); // 'my default'
```

## Check if a setting exists

```php
Settings::has('foo');
```

## Remove a setting from storage

```php
Settings::forget('foo');
```

## Boolean settings

```php
Settings::set('app.debug', true);

Settings::isTrue('app.debug'); // true
Settings::isFalse('app.debug'); // false

Settings::set('app.debug', false);
Settings::isFalse('app.debug'); // true
```

## Retrieve all settings

Using `all`, you can retrieve all the stored settings, which will be returned as a collection. You may also retrieve a subset of settings
by passing in an array of keys to retrieve.

```php
Settings::all();

// Subset of settings
Settings::all(['foo', 'bar']);
```

The collection of settings returned from this method will contain objects structured like this:

```json
{
    "id": 1,
    "key": "foo",
    "value": "bar",
    "original_key": "foo"
}
```

> {tip} The `original_key` property is set by settings to reflect the key that is used in the database.

> {tip} If you'd like to retrieve all settings that do not have a context, you may provide a `false` value for a context: `settings()->context(false)->all()`

## Flushing settings

Multiple settings can be deleted at one time using the `flush` method on settings. It works similar to `forget`, however
it is not able to flush the cache for each setting, unless you pass in a subset of keys to flush.

```php
Settings::flush();

// Flush a subset of settings
Settings::flush(['foo', 'bar']);
```
