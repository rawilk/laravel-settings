<?php

namespace Rawilk\Settings\Tests\Support\Drivers;

use Rawilk\Settings\Contracts\Driver;

class CustomDriver implements Driver
{
    public function forget($key): void
    {
        //
    }

    public function get(string $key, $default = null)
    {
        return $default;
    }

    public function has($key): bool
    {
        return true;
    }

    public function set(string $key, $value = null): void
    {
        //
    }
}
