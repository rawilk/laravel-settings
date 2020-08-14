---
title: Basic Usage
sort: 1
---

You can interact with settings via the `Settings` facade, or by using the `settings()` helper function, which returns an instance of `Rawilk\Settings\Settings`.

## Setting a value

<x-code lang="php">
// Create a new setting
Settings::set('foo', 'bar');

// Update an existing setting
Settings::set('foo', 'updated value');
</x-code>

## Retrieving a value
<x-code lang="php">
Settings::get('foo');

// Retrieve a non-persisted setting
Settings::get('not persisted', 'my default'); // 'my default'
</x-code>

## Check if a setting exists
<x-code lang="php">Settings::has('foo');</x-code>

## Remove a setting from storage
<x-code lang="php">Settings::forget('foo');</x-code>

## Boolean settings
<x-code lang="php">
Settings::set('app.debug', true);

Settings::isTrue('app.debug'); // true
Settings::isFalse('app.debug'); // false

Settings::set('app.debug', false);
Settings::isFalse('app.debug'); // true
</x-code>
