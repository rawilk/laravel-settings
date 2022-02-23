<?php

namespace Rawilk\Settings\Drivers;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Rawilk\Settings\Contracts\Driver;

class DatabaseDriver implements Driver
{
    public function __construct(protected Connection $connection, protected string $table)
    {
    }

    public function forget($key): void
    {
        $this->table()->where('key', $key)->delete();
    }

    public function get(string $key, $default = null)
    {
        $value = $this->table()->where('key', $key)->value('value');

        return $value ?? $default;
    }

    public function has($key): bool
    {
        return $this->table()->where('key', $key)->exists();
    }

    public function set(string $key, $value = null): void
    {
        try {
            $this->table()->insert(compact('key', 'value'));
        } catch (\Exception) {
            $this->table()->where('key', $key)->update(compact('value'));
        }
    }

    protected function table(): Builder
    {
        return $this->connection->table($this->table);
    }
}
