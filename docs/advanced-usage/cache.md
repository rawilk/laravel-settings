---
title: Cache
sort: 6
---

## Introduction

When caching is enabled, we will cache the settings forever. For most applications this is probably fine. However, you may want to set an expiration on settings for various reasons.

## Set Cache TTL Globally

To set the same cache ttl for each setting, you may use the `cache_ttl` config key in the `config/settings.php` config file.

```php
// config/settings.php

'cache_ttl' => now()->addDay(),
```

We've now configured the package to only cache settings for one day. After that, a fresh value will be retrieved.

If you want to use Laravel's [stale while revalidate](https://laravel.com/docs/13.x/cache#swr) cache pattern, you can set the `cache_ttl` value to an array.

```php
// config/settings.php

'cache_ttl' => [5, 10],
```

When an array value is set, we will use `Cache::flexible()` automatically for you.

## Set Cache TTL at Runtime

You may also set the ttl for the settings cache at runtime by using the `cacheItemsFor()` method on settings. You provide it the same values you can set in the config. 

```php
settings()->cacheItemsFor(now()->addDay());
```

> {note} Doing this will use the same ttl value for any further setting calls for the current request, unless you change it again.

## Use a TTL Once

If you only need a cache ttl to apply to a single setting, you may provide the ttl value as an optional third parameter to `get()`, `set()`, `isFalse()`, and `isTrue()`. 

```php
use Rawilk\Settings\Facades\Settings;

Settings::get('my-setting', cacheTtl: [5, 10]);
```
