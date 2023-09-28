<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests\Support\Drivers;

use Illuminate\Contracts\Support\Arrayable;
use Rawilk\Settings\Contracts\Driver;

final class CustomDriver implements Driver
{
    public function forget($key, $teamId = null): void
    {
        //
    }

    public function get(string $key, $default = null, $teamId = null)
    {
        return $default;
    }

    public function has($key, $teamId = null): bool
    {
        return true;
    }

    public function set(string $key, $value = null, $teamId = null): void
    {
        //
    }

    public function all($teamId = null, $keys = null): array|Arrayable
    {
        return [];
    }
}
