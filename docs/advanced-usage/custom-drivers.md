---
title: Custom Drivers
sort: 1
---

You may extend settings to use your own drivers for storing and retrieving settings, such as using a JSON
or XML file. To do so, you will need to add your driver's configuration in the `drivers` key in the `config/settings.php`
config file, with the following minimum configuration:

To start, create your custom driver class and have it implement the `Driver` interface. We'll showcase a mock JSON driver as an example:

```php
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Rawilk\Settings\Contracts\Driver;

class JsonDriver implements Driver
{
    public function __construct(protected string $path) 
    {
    }
    
    public function forget($key, $teamId = null): void
    {
        $settings = $this->allSettings();

        Arr::forget($settings, $this->getStoreKey($key, $teamId));

        $this->save($settings);
    }

    public function get(string $key, $teamId = null)
    {
        return Arr::get($this->allSettings(), $this->getStoreKey($key, $teamId));
    }

    public function all($teamId = null, $keys = null): array|Arrayable
    {
        $settings = $this->allSettings();

        if ($teamId !== null) {
            $settings = Arr::get($settings, "team_{$teamId}", []);
        }

        if ($keys) {
            return Arr::only($settings, (array) $keys);
        }

        return $settings;
    }

    public function has($key, $teamId = null): bool
    {
        return Arr::has($this->allSettings(), $this->getStoreKey($key, $teamId));
    }

    public function set(string $key, $value = null, $teamId = null): void
    {
        $settings = $this->allSettings();

        Arr::set($settings, $this->getStoreKey($key, $teamId), $value);

        $this->save($settings);
    }

    public function flush($teamId = null, $keys = null): void
    {
        if ($teamId === null && $keys === null) {
            $this->save([]);

            return;
        }

        $settings = $this->allSettings();

        if ($teamId !== null && $keys === null) {
            Arr::forget($settings, "team_{$teamId}");
        } else {
            // Handle specific keys or other logic if needed
        }

        $this->save($settings);
    }

    protected function allSettings(): array
    {
        if (! File::exists($this->path)) {
            return [];
        }

        return json_decode(File::get($this->path), true) ?? [];
    }

    protected function save(array $settings): void
    {
        File::put($this->path, json_encode($settings, JSON_PRETTY_PRINT));
    }

    protected function getStoreKey(string $key, $teamId = null): string
    {
        return $teamId ? "team_{$teamId}.{$key}" : $key;
    }
}
```

> {note} Your custom drivers **do not need to handle encryption or caching**; the settings service will handle that for you.

With your custom driver created, you need to extend the settings service. A good place to do this would be in a service provider:

```php
// app/Providers/AppServiceProvider.php

use App\Settings\Drivers\JsonDriver;
use Rawilk\Settings\Facades\Settings;

public function boot(): void
{
    Settings::extend('json', function ($app, array $config) {
        return new JsonDriver(path: storage_path('settings.json'));
    });
}
```

Finally, you need to update the `driver` in the config file to your new driver name if you want it to be used as the default driver.

```php
// config/settings.php

'driver' => 'json',
```
