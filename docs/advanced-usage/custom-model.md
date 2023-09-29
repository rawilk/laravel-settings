---
title: Custom Eloquent Model
sort: 2
---

Laravel Settings ships with an Eloquent model for Settings already, but you are free to use your own model. You can either extend
the package's model, or create your own. The only requirement is that it implements the `Rawilk\Settings\Contracts\Setting` interface.

Here is what the interface looks like:

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

Once you have your custom model created, you need to define it in the `eloquent` driver in `config/settings.php`.

```php
'drivers' => [
    'eloquent' => [
        'driver' => 'eloquent',
        'model' => YourCustomModel::class,
    ],
],
```
