---
title: Overriding Config
sort: 2
---

A common scenario you may run into is overriding a config value from a setting value you have stored in the database.
The best place to do this would be in the `boot()` method in a service provider, however you could also override the
value before your application references the config value.

```php
public function boot()
{
    config(['app.timezone' => Settings::get('app.timezone', 'UTC')]);
}
```

You can store the settings you are using to override config values with any keys you want, but for clarity you should store
them as the same key that is used in the config.
