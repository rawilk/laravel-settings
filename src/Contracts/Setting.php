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
