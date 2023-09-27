<?php

declare(strict_types=1);

namespace Rawilk\Settings\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Rawilk\Settings\Contracts\Setting as SettingContract;

class Setting extends Model implements SettingContract
{
    protected ?string $teamForeignKey = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('settings.table'));
        $this->teamForeignKey = config('settings.team_foreign_key');
    }

    protected $guarded = ['id'];

    public $timestamps = false;

    public static function getValue(string $key, $default = null, $teamId = null)
    {
        $value = static::query()
            ->where('key', $key)
            ->when(
                $teamId !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.team_foreign_key'),
                    $teamId,
                ),
            )
            ->value('value');

        return $value ?? $default;
    }

    public static function has($key, $teamId = null): bool
    {
        return static::query()
            ->where('key', $key)
            ->when(
                $teamId !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.team_foreign_key'),
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
                $teamId !== false,
                fn (Builder $query) => $query->where(
                    static::make()->getTable() . '.' . config('settings.team_foreign_key'),
                    $teamId,
                ),
            )
            ->delete();
    }

    public static function set(string $key, $value = null, $teamId = null)
    {
        $data = ['key' => $key];

        if ($teamId !== false) {
            $data[config('settings.team_foreign_key')] = $teamId;
        }

        return static::updateOrCreate($data, compact('value'));
    }
}
