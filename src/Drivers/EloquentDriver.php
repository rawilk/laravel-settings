<?php

namespace Rawilk\Settings\Drivers;

use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Contracts\Setting;

class EloquentDriver implements Driver
{
    protected Setting $model;

    public function __construct(Setting $model)
    {
        $this->model = $model;
    }

    public function forget($key): void
    {
        $this->model::removeSetting($key);
    }

    public function get(string $key, $default = null)
    {
        return $this->model::getValue($key, $default);
    }

    public function has($key): bool
    {
        return $this->model::has($key);
    }

    public function set(string $key, $value = null): void
    {
        $this->model::set($key, $value);
    }
}
