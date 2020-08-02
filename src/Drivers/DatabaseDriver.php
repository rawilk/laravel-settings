<?php

namespace Rawilk\Settings\Drivers;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Rawilk\Settings\Contracts\Driver;

class DatabaseDriver implements Driver
{
    protected Connection $connection;
    protected string $table;

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
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
        } catch (\Exception $e) {
            $this->table()->where('key', $key)->update(compact('value'));
        }
    }

    protected function table(): Builder
    {
        return $this->connection->table($this->table);
    }
}
