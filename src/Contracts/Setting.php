<?php

declare(strict_types=1);

namespace Rawilk\Settings\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface Setting
{
    public static function getValue(string $key, $default = null, $morphId = null, $morphType = null);

    public static function getAll($morphId = null, $morphType = null, $keys = null): array|Arrayable;

    public static function has($key, $morphId = null, $morphType = null): bool;

    public static function removeSetting($key, $morphId = null, $morphType = null): void;

    public static function set(string $key, $value = null, $morphId = null, $morphType = null);

    public static function flush($morphId = null, $morphType = null, $keys = null);
}
