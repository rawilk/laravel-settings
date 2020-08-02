<?php

namespace Rawilk\Settings\Contracts;

interface Driver
{
    public function forget($key);

    public function get(string $key, $default = null);

    public function has($key): bool;

    public function set(string $key, $value = null);
}
