<?php

declare(strict_types=1);

namespace Rawilk\Settings\Drivers;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Rawilk\Settings\Contracts\Driver;

class DatabaseDriver implements Driver
{
    public function __construct(
        protected Connection $connection,
        protected string $table,
        protected ?string $teamForeignKey = null,
    ) {
    }

    public function forget($key, $teamId = null): void
    {
        $this->db()
            ->where('key', $key)
            ->when(
                $teamId !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->teamForeignKey}", $teamId)
            )
            ->delete();
    }

    public function get(string $key, $default = null, $teamId = null)
    {
        $value = $this->db()
            ->where('key', $key)
            ->when(
                $teamId !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->teamForeignKey}", $teamId)
            )
            ->value('value');

        return $value ?? $default;
    }

    public function has($key, $teamId = null): bool
    {
        return $this->db()
            ->where('key', $key)
            ->when(
                $teamId !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->teamForeignKey}", $teamId)
            )
            ->exists();
    }

    public function set(string $key, $value = null, $teamId = null): void
    {
        $data = [
            'key' => $key,
        ];

        if ($teamId !== false) {
            $data[$this->teamForeignKey] = $teamId;
        }

        $this->db()->updateOrInsert($data, compact('value'));
    }

    protected function db(): Builder
    {
        return $this->connection->table($this->table);
    }
}
