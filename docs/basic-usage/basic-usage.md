---
title: Basic Usage
sort: 1
---

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
```
phpSettings::forget('foo');
```

## Boolean settings
```php
Settings::set('app.debug', true);

Settings::isTrue('app.debug'); // true
Settings::isFalse('app.debug'); // false

Settings::set('app.debug', false);
Settings::isFalse('app.debug'); // true
```
