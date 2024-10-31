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
        protected ?string $morphName = null,
    ) {}

    public function forget($key, $morphId = null, $morphType = null): void
    {
        $this->db()
            ->where('key', $key)
            ->when(
                $morphId !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->morphName}_id", $morphId)
            )
            ->when(
                $morphType !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->morphName}_type", $morphType)
            )
            ->delete();
    }

    public function get(string $key, $default = null, $morphId = null, $morphType = null)
    {
        $value = $this->db()
            ->where('key', $key)
            ->when(
                $morphId !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->morphName}_id", $morphId)
            )
            ->when(
                $morphType !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->morphName}_type", $morphType)
            )
            ->value('value');

        return $value ?? $default;
    }

    public function all($morphId = null, $morphType = null, $keys = null): array|Arrayable
    {
        return $this->baseBulkQuery($morphId, $morphType, $keys)->get();
    }

    public function has($key, $morphId = null, $morphType = null): bool
    {
        return $this->db()
            ->where('key', $key)
            ->when(
                $morphId !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->morphName}_id", $morphId)
            )
            ->when(
                $morphType !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->morphName}_type", $morphType)
            )
            ->exists();
    }

    public function set(string $key, $value = null, $morphId = null, $morphType = null): void
    {
        $data = [
            'key' => $key,
        ];

        if ($morphId !== false) {
            $data["{$this->morphName}_id"] = $morphId;
        }

        if ($morphType !== false) {
            $data["{$this->morphName}_type"] = $morphType;
        }

        $this->db()->updateOrInsert($data, compact('value'));
    }

    public function flush($morphId = null, $keys = null, $morphType = null): void
    {
        $this->baseBulkQuery($morphId, $morphType, $keys)->delete();
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

    private function baseBulkQuery($morphId, $morphType, $keys): Builder
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
                $morphId !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->morphName}_id", $morphId)
            )
            ->when(
                $morphType !== false,
                fn (Builder $query) => $query->where("{$this->table}.{$this->morphName}_type", $morphType)
            );
    }
}
