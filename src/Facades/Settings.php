<?php

declare(strict_types=1);

namespace Rawilk\Settings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rawilk\Settings\Settings
 *
 * @method static \Rawilk\Settings\Settings context(null|\Rawilk\Settings\Support\Context $context = null)
 * @method static null|mixed forget(string|\BackedEnum $key)
 * @method static mixed get(string|\BackedEnum $key, null|mixed $default = null)
 * @method static \Illuminate\Support\Collection all($keys)
 * @method static bool isFalse(string|\BackedEnum $key, bool|int|string $default = false)
 * @method static bool isTrue(string|\BackedEnum $key, bool|int|string $default = true)
 * @method static bool has(string|\BackedEnum $key)
 * @method static null|mixed set(string|\BackedEnum $key, null|mixed $value = null)
 * @method static void flush($keys)
 * @method static self disableCache()
 * @method static self enableCache()
 * @method static self temporarilyDisableCache()
 * @method static self disableEncryption()
 * @method static self enableEncryption()
 * @method static null|mixed getMorphId()
 * @method static self setMorphs(mixed $id, mixed $morphType = null)
 * @method static self enableMorphs()
 * @method static self disableMorphs()
 * @method static bool morphsAreEnabled()
 * @method static self usingMorph(mixed $morphId, mixed $morphType = null)
 * @method static self withoutMorphs()
 * @method static \Rawilk\Settings\Contracts\KeyGenerator getKeyGenerator()
 * @method static string cacheKeyForSetting(string $key)
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Rawilk\Settings\Settings::class;
    }
}
