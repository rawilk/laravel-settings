<?php

declare(strict_types=1);

namespace Rawilk\Settings\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface Driver
{
    public function forget($key, $morphId = null, $morphType = null);

    public function get(string $key, $default = null, $morphId = null, $morphType = null);

    public function all($morphId = null, $morphType = null, $keys = null): array|Arrayable;

    public function has($key, $morphId = null, $morphType = null): bool;

    public function set(string $key, $value = null, $morphId = null, $morphType = null);

    public function flush($morphId = null, $keys = null, $morphType = null);
}
