<?php

namespace Rawilk\Settings\Contracts;

interface Setting
{
    public static function getValue(string $key, $default = null);

    public static function has($key): bool;

    public static function removeSetting($key);

    public static function set(string $key, $value = null);
}
