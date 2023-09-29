---
title: Custom Drivers
sort: 1
---

You can easily extend settings to use your own drivers for storing and retrieving settings, such as using a json
or xml file. To do so, you will need to add your driver's configuration in the `drivers` key in the `config/settings.php`
config file, with the following minimum configuration:

```php
'drivers' => [
    // ... other drivers
    'custom' => [
        'driver' => 'custom',
        // driver specific configuration
    ],
],
```

> {note} Replace **custom** with your driver name.

You will then need to tell settings about your custom driver in a service provider:

```php
app('SettingsFactory')->extend('custom', fn ($app, $config) => new CustomDriver($config));

// You can also set your custom driver as the default driver here, or in the config/settings.php config file:
app('SettingsFactory')->setDefaultDriver('custom');
```

Any custom drivers you make must implement the `Rawilk\Settings\Contracts\Driver` interface. Here is what
the interface looks like:

```php
<?php

declare(strict_types=1);

namespace Rawilk\Settings\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface Driver
{
    public function forget($key, $teamId = null);

    public function get(string $key, $default = null, $teamId = null);

    public function all($teamId = null, $keys = null): array|Arrayable;

    public function has($key, $teamId = null): bool;

    public function set(string $key, $value = null, $teamId = null);

    public function flush($teamId = null, $keys = null);
}
```

> {note} Your custom drivers **do not need to handle encryption or caching**; the settings service will handle that for you.
