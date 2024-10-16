<?php

declare(strict_types=1);

namespace Rawilk\Settings\Drivers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Facades\Settings;

class DatabaseDriver implements Driver
{
    public function __construct(
        protected Connection $connection,
        protected string $table,
        protected ?string $teamForeignKey = null,
    ) {}

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

    public function all($teamId = null, $keys = null): array|Arrayable
    {
        return $this->baseBulkQuery($teamId, $keys)->get();
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

    public function flush($teamId = null, $keys = null): void
    {
        $this->baseBulkQuery($teamId, $keys)->delete();
    }

    protected function db(): Builder
    {
        return $this->connection->table($this->table);
    }

    protected function normalizeKeys($keys): string|Collection|bool
    {
        if (is_bool($keys)) {
            return $keys;
        }

        if (is_string($keys)) {
            return $keys;
        }

        return collect($keys)->flatten()->filter();
    }

    private function baseBulkQuery($teamId, $keys): Builder
    {
        $keys = $this->normalizeKeys($keys);

        return $this->db()
            ->when(
                // False means we want settings without a context set.
                $keys === false,
                fn (Builder $query) => $query->where('key', 'NOT LIKE', '%' . Settings::getKeyGenerator()->contextPrefix() . '%'),
            )
            ->when(
                // When keys is a string, we're trying to do a partial lookup for context
                is_string($keys),
                fn (Builder $query) => $query->where('key', 'LIKE', "%{$keys}"),
            )
            ->when(
                $keys instanceof Collection && $keys->isNotEmpty(),
                fn (Builder $query) => $query->whereIn('key', $keys),
            )
            ->when(
                $teamId !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->teamForeignKey}", $teamId)
            );
    }
}
