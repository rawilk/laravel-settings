---
title: Settings
sort: 1
---

The methods shown here are available on the `Rawilk\Settings\Settings` service class. You can call any of the methods via either the Settings Facade or the `settings()` helper function. Several methods return an instance of `Settings`, so they are chainable to each other.

## Persistence

### forget

Remove a persisted setting from storage.

```php
public function forget(string|UnitEnum $key): mixed
```

### get

Retrieve a setting from storage.

```php
public function get(
    string|UnitEnum $key,
    mixed $default = null,
    null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
): mixed 
```

### has

Determine if a setting has been persisted to storage.

```php
public function has(string|UnitEnum $key): bool
```

> {note} Any caching used to store settings is not checked with `has()`. It will always perform a database query when using database-based drivers.

### set

Create or update a setting and persist to storage.

```php
public function set(
    string|UnitEnum $key,
    mixed $value = null,
    null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
): mixed
```

### isFalse

Determine if a setting is set to a falsy value. Returns true if the value is `false`, `0`, or `'0'`.

```php
public function isFalse(
    string|UnitEnum $key,
    mixed $default = false,
    null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
): bool
```

### isTrue

Determine if a setting is set to a truthy value. Returns true if the value is `true`, `1`, or `'1'`.

```php
public function isTrue(
    string|UnitEnum $key,
    mixed $default = true,
    null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
): bool
```

### all

Retrieve all stored settings, or a subset of settings with a `$keys` parameter.

```php
use Illuminate\Support\Collection;

/**
 * @param array|string|null $keys Only return a subset of settings.
 * @return Collection<int, object>
 */
public function all($keys = null): Collection
```

> {note} The `KeyValueContextSerializer` context serializer must be used for this to work properly with subsets of keys.

### flush

Flush all settings or a subset of settings from storage.

```php
/**
 * @param array|string|null $keys Only flush a subset of settings.
 */
public function flush($keys = null): mixed
```

## Context

### context

Set the context for the current operation. Omit or set `$context` to `null` to remove context. Note: The context set here is only applied to a single settings call or settings instance.

```php
use Rawilk\Settings\Support\Context;

public function context(Context|bool|null $context = null): static
```

### withContext

Scope the context of settings within a callback function.

```php
use Rawilk\Settings\Support\Context;

public function withContext(Context|bool|null $context, Closure $callback): mixed
```

Note: You will need to use the same settings instance within the callback to ensure the context is applied correctly. We provide tghe instance of the settings service to your callback:

```php
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Settings as SettingsService;
use Rawilk\Settings\Support\Context;

Settings::withContext(new Context(['foo' => 'bar']), function (SettingsService $settings) {
    $settings->set('foo', 'bar');
});
```

## Caching

### cacheKeyForSetting

Get the correct cache key for a given setting.

```php
public function cacheKeyForSetting(string|UnitEnum $key): string
```

### enableCache

Enable caching for settings operations.

```php
public function enableCache(): static
```

> {note} This persists between settings calls.

### disableCache

Disable caching for settings operations.

```php
public function disableCache(): static
```

> {note} This persists between settings calls.

### withoutCache

Scope operations to disable the cache within a callback.

```php
public function withoutCache(Closure $callback): mixed
```

### cacheItemsFor

Set the TTL for items cached by settings.

```php
public function cacheItemsFor(null|int|array|Closure|DateTimeInterface|DateInterval $ttl): static
```

> {note} This persists between settings calls.

### prefixCacheWith

Define a cache prefix for setting keys on the fly. Note: this only lasts for a single settings call or settings instance.

```php
public function prefixCacheWith(string $prefix): static
```

### useCacheStore

Define a cache store to use for settings on the fly. Note: this only lasts for a single settings call or settings instance.

```php
public function useCacheStore(null|string|UnitEnum $store = null): static
```

## Teams

### disableTeams

Disable teams completely.

```php
public function disableTeams(): static
```

> {note} This persists between settings calls.

### enableTeams

Enable the teams feature.

```php
public function enableTeams(): static
```

> {note} This persists between settings calls.

### usingTeam

Scope operations to a specific team. Note: This only applies if teams are enabled.

```php
public function usingTeam(mixed $team, ?Closure $callback = null): mixed
```

### noTeam

Scope operations to no team (`team_id = null`). Note: This is only applies if teams are enabled.

```php
public function noTeam(?Closure $callback = null): mixed
```

## Encryption

### enableEncryption

Enable value encryption for settings.

```php
public function enableEncryption(): static
```

> {note} This persists between settings calls.

### disableEncryption

Prevent settings from encrypting values.

```php
public function disableEncryption(): static
```

> {note} This persists between settings calls.

### withoutEncryption

Disable encryption for setting values within a callback.

```php
public function withoutEncryption(Closure $callback): mixed
```

## Drivers

### extend

Register a custom driver for settings.

```php
public function extend(string $driver, Closure $callback): static
```

### usingDriver

Use a specific driver for a single callback.

```php
public function usingDriver(Driver|string|UnitEnum $driver, Closure $callback): mixed
```
