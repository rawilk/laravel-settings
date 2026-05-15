<?php

declare(strict_types=1);

namespace Rawilk\Settings\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Rawilk\Settings\Contracts\Setting as SettingContract;
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Support\SettingsConfig;

/**
 * @property int $id
 * @property string $key
 * @property mixed $value
 * @property int|string|null $team_id
 */
class Setting extends Model implements SettingContract
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(SettingsConfig::getSettingsTable());
    }

    public static function getValue(string $key, $teamId = null)
    {
        return static::query()
            ->where('key', $key)
            ->when(
                settings()->teamsAreEnabled() && $teamId !== false,
                fn (Builder $query) => $query->where(
                    static::getTeamColumn(),
                    $teamId,
                ),
            )
            ->value('value');
    }

    public static function getAll($teamId = null, $keys = null): array|Arrayable
    {
        return static::baseBulkQuery($teamId, $keys)->get();
    }

    public static function has($key, $teamId = null): bool
    {
        return static::query()
            ->where('key', $key)
            ->when(
                settings()->teamsAreEnabled() && $teamId !== false,
                fn (Builder $query) => $query->where(
                    static::getTeamColumn(),
                    $teamId,
                ),
            )
            ->exists();
    }

    public static function removeSetting($key, $teamId = null): void
    {
        static::query()
            ->where('key', $key)
            ->when(
                settings()->teamsAreEnabled() && $teamId !== false,
                fn (Builder $query) => $query->where(
                    static::getTeamColumn(),
                    $teamId,
                ),
            )
            ->delete();
    }

    public static function set(string $key, $value = null, $teamId = null)
    {
        $data = ['key' => $key];

        if (settings()->teamsAreEnabled() && $teamId !== false) {
            $data[SettingsConfig::getTeamsForeignKey()] = $teamId;
        }

        return static::updateOrCreate($data, compact('value'));
    }

    public static function flush($teamId = null, $keys = null): void
    {
        static::baseBulkQuery($teamId, $keys)->delete();
    }

    protected static function baseBulkQuery($teamId, $keys): Builder
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
                settings()->teamsAreEnabled() && $teamId !== false,
                fn (Builder $query) => $query->where(
                    static::getTeamColumn(),
                    $teamId,
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

    protected static function getTeamColumn(): string
    {
        return SettingsConfig::getSettingsTable() . '.' . SettingsConfig::getTeamsForeignKey();
    }
}
