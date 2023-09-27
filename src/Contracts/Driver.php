<?php

declare(strict_types=1);

namespace Rawilk\Settings\Contracts;

interface Driver
{
    public function forget($key, $teamId = null);

    public function get(string $key, $default = null, $teamId = null);

    public function has($key, $teamId = null): bool;

    public function set(string $key, $value = null, $teamId = null);
}
