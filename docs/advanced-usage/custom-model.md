---
title: Custom Eloquent Model
sort: 2
---

Laravel Settings ships with an Eloquent model for Settings already, but you are free to use your own model. You can either extend
the package's model, or create your own. The only requirement is that it implements the `Rawilk\Settings\Contracts\Setting` interface.

Here is what the interface looks like:

```php
namespace Rawilk\Settings\Contracts;

interface Setting
{
    public static function getValue(string $key, $default = null);

    public static function has($key): bool;

    public static function removeSetting($key);

    public static function set(string $key, $value = null);
}
```

Once you have your custom model created, you need to define it in the `eloquent` driver in `config/settings.php`.

```php
'drivers' => [
    'eloquent' => [
        'driver' => 'eloquent',
        'model' => YourCustomModel::class,
    ],
],
```
