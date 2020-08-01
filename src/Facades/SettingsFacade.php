<?php

namespace Rawilk\Settings\Facades;

use Illuminate\Support\Facades\Facade;
use Rawilk\Settings\Settings;

/**
 * @see \Rawilk\Settings\Settings
 */
class SettingsFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Settings::class;
    }
}
