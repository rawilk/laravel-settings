<?php

declare(strict_types=1);

namespace Rawilk\Settings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rawilk\Settings\Settings
 *
 * @method static \Rawilk\Settings\Settings context(null|\Rawilk\Settings\Support\Context $context = null)
 * @method static null|mixed forget($key)
 * @method static mixed get(string $key, null|mixed $default = null)
 * @method static \Illuminate\Support\Collection all($keys)
 * @method static bool isFalse(string $key, bool|int|string $default = false)
 * @method static bool isTrue(string $key, bool|int|string $default = true)
 * @method static bool has($key)
 * @method static null|mixed set(string $key, null|mixed $value = null)
 * @method static void flush($keys)
 * @method static self disableCache()
 * @method static self enableCache()
 * @method static self temporarilyDisableCache()
 * @method static self disableEncryption()
 * @method static self enableEncryption()
 * @method static null|mixed getTeamId()
 * @method static self setTeamId(mixed $id)
 * @method static self enableTeams()
 * @method static self disableTeams()
 * @method static bool teamsAreEnabled()
 * @method static \Rawilk\Settings\Contracts\KeyGenerator getKeyGenerator()
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Rawilk\Settings\Settings::class;
    }
}
