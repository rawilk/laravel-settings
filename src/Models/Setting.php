<?php

namespace Rawilk\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Rawilk\Settings\Contracts\Setting as SettingContract;

class Setting extends Model implements SettingContract
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('settings.table'));
    }

    protected $guarded = ['id'];

    public $timestamps = false;

    public static function getValue(string $key, $default = null)
    {
        $value = self::where('key', $key)->value('value');

        return $value ?? $default;
    }

    public static function has($key): bool
    {
        return self::where('key', $key)->exists();
    }

    public static function removeSetting($key): void
    {
        self::where('key', $key)->delete();
    }

    public static function set(string $key, $value = null)
    {
        return self::updateOrCreate(compact('key'), compact('value'));
    }
}
