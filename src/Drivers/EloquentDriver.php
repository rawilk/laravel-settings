<?php

declare(strict_types=1);

namespace Rawilk\Settings\Drivers;

use Illuminate\Contracts\Support\Arrayable;
use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Contracts\Setting;

class EloquentDriver implements Driver
{
    public function __construct(protected Setting $model) {}

    public function forget($key, $morphId = null, $morphType = null): void
    {
        $this->model::removeSetting($key, $morphId, $morphType);
    }

    public function get(string $key, $default = null, $morphId = null, $morphType = null)
    {
        return $this->model::getValue($key, $default, $morphId, $morphType);
    }

    public function all($morphId = null, $morphType = null, $keys = null): array|Arrayable
    {
        return $this->model::getAll($morphId, $morphType, $keys);
    }

    public function has($key, $morphId = null, $morphType = null): bool
    {
        return $this->model::has($key, $morphId, $morphType);
    }

    public function set(string $key, $value = null, $morphId = null, $morphType = null): void
    {
        $this->model::set($key, $value, $morphId, $morphType);
    }

    public function flush($morphId = null, $keys = null, $morphType = null): void
    {
        $this->model::flush($morphId, $morphType, $keys);
    }
}
