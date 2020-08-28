---
title: Performance Tips
sort: 1
---

By default, all settings are cached after accessing them for the first time or after accessing the setting again after it has been updated.
You are free to turn caching off, but you might notice a performance hit on larger apps that are storing a large amount of settings or
on apps that are retrieving many settings on each page load.

As always, if you choose to bypass the provided methods for setting and removing settings, you will need to flush the cache manually for
each setting you manipulate manually. To determine the cache key for a setting key, you should use the `Rawilk\Settings\Support\KeyGenerator` to
generate the md5 version of the setting's key:

```php
(new KeyGenerator(new ContextSerializer))->generate($key, $context);
```

You will also need to prefix it with the `cache_key_prefix` found in `config/settings.php`.
