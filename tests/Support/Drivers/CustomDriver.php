<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests\Support\Drivers;

use Illuminate\Contracts\Support\Arrayable;
use Rawilk\Settings\Contracts\Driver;

final class CustomDriver implements Driver
{
    public function forget($key, $morphId = null, $morphType = null): void
    {
        //
    }

    public function get(string $key, $default = null, $morphId = null, $morphType = null)
    {
        return $default;
    }

    public function has($key, $morphId = null, $morphType = null): bool
    {
        return true;
    }

    public function set(string $key, $value = null, $morphId = null, $morphType = null): void
    {
        //
    }

    public function all($morphId = null, $morphType = null, $keys = null): array|Arrayable
    {
        return [];
    }

    public function flush($morphId = null, $keys = null, $morphType = null): void {}
}
