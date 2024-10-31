<?php

declare(strict_types=1);

namespace Rawilk\Settings\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Rawilk\Settings\Contracts\Setting as SettingContract;
use Rawilk\Settings\Facades\Settings;

/**
 * @property int $id
 * @property string $key
 * @property mixed $value
 * @property int|string|null $model_id
 * @property int|string|null $model_type
 */
class Setting extends Model implements SettingContract
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('settings.table'));
    }

    public static function getValue(string $key, $default = null, $morphId = null, $morphType = null)
    {
        $value = static::query()
            ->where('key', $key)
            ->when(
                $morphId !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.morph_name') . '_id',
                    $morphId,
                ),
            )
            ->when(
                $morphType !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.morph_name') . '_type',
                    $morphType,
                ),
            )
            ->value('value');

        return $value ?? $default;
    }

    public static function getAll($morphId = null, $morphType = null, $keys = null): array|Arrayable
    {
        return static::baseBulkQuery($morphId, $morphType, $keys)->get();
    }

    public static function has($key, $morphId = null, $morphType = null): bool
    {
        return static::query()
            ->where('key', $key)
            ->when(
                $morphId !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.morph_name') . '_id',
                    $morphId,
                ),
            )
            ->when(
                $morphType !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.morph_name') . '_type',
                    $morphType,
                ),
            )
            ->exists();
    }

    public static function removeSetting($key, $morphId = null, $morphType = null): void
    {
        static::query()
            ->where('key', $key)
            ->when(
                $morphId !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.morph_name') . '_id',
                    $morphId,
                ),
            )
            ->when(
                $morphType !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.morph_name') . '_type',
                    $morphType,
                ),
            )
            ->delete();
    }

    public static function set(string $key, $value = null, $morphId = null, $morphType = null)
    {
        $data = ['key' => $key];

        if ($morphId !== false) {
            $data[config('settings.morph_name') . '_id'] = $morphId;
        }

        if ($morphType !== false) {
            $data[config('settings.morph_name') . '_type'] = $morphType;
        }

        return static::updateOrCreate($data, compact('value'));
    }

    public static function flush($morphId = null, $morphType = null, $keys = null): void
    {
        static::baseBulkQuery($morphId, $morphType, $keys)->delete();
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function baseBulkQuery($morphId, $morphType, $keys): Builder
    {
        $keys = static::normalizeKeys($keys);

        return static::query()
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
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.morph_name') . '_id',
                    $morphId,
                ),
            )->when(
                $morphType !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.morph_name') . '_type',
                    $morphType,
                ),
            );

    }

    protected static function normalizeKeys($keys): string|Collection|bool
    {
        if (is_bool($keys)) {
            return $keys;
        }

        if (is_string($keys)) {
            return $keys;
        }

        return collect($keys)->flatten()->filter();
    }
}
