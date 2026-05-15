<?php

declare(strict_types=1);

namespace Rawilk\Settings\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Contracts\KeyGenerator;
use Rawilk\Settings\Contracts\ValueSerializer;
use Rawilk\Settings\Settings as SettingsService;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\PendingSettings;

/**
 * @see PendingSettings
 * @see SettingsService
 *
 * @method static static context(null|Context|bool $context = null)
 * @method static mixed withContext(null|Context|bool $context, \Closure $callback)
 * @method static Driver driver(null|string|\UnitEnum $driver = null)
 * @method static static setDriver(Driver $driver)
 * @method static mixed usingDriver(Driver|string|\UnitEnum $driver, callable $callback)
 * @method static static extend(string $driver, \Closure $callback)
 * @method static mixed forget(string|\UnitEnum $key)
 * @method static mixed get(string|\UnitEnum $key, mixed $default = null, null|int|\Closure|\DateTimeInterface|\DateInterval|array $cacheTtl = null)
 * @method static Collection all(mixed $keys = null)
 * @method static bool isFalse(string|\UnitEnum $key, mixed $default = false, null|int|\Closure|\DateTimeInterface|\DateInterval|array $cacheTtl = null)
 * @method static bool isTrue(string|\UnitEnum $key, mixed $default = true, null|int|\Closure|\DateTimeInterface|\DateInterval|array $cacheTtl = null)
 * @method static bool has(string|\UnitEnum $key)
 * @method static mixed set(string|\UnitEnum $key, mixed $value = null, null|int|\Closure|\DateTimeInterface|\DateInterval|array $cacheTtl = null)
 * @method static mixed flush(mixed $keys = null)
 * @method static static disableCache()
 * @method static static enableCache()
 * @method static mixed withoutCache(\Closure $callback)
 * @method static static cacheItemsFor(null|int|array|\Closure|\DateTimeInterface|\DateInterval $ttl)
 * @method static static cacheDefaultValue(bool $condition = true)
 * @method static static prefixCacheWith(string $prefix)
 * @method static static useCacheStore(null|string|\UnitEnum $store = null)
 * @method static string cacheKeyForSetting(string|\UnitEnum $key)
 * @method static static disableEncryption()
 * @method static static enableEncryption()
 * @method static mixed withoutEncryption(\Closure $callback)
 * @method static static enableTeams()
 * @method static static disableTeams()
 * @method static bool teamsAreEnabled()
 * @method static mixed usingTeam(mixed $teamId, ?\Closure $callback = null)
 * @method static mixed noTeam(?\Closure $callback = null)
 * @method static static defaultTeam(mixed $team, ?\Closure $callback = null)
 * @method static KeyGenerator getKeyGenerator()
 * @method static static setKeyGenerator(KeyGenerator $generator)
 * @method static ValueSerializer getValueSerializer()
 * @method static static setValueSerializer(ValueSerializer $serializer)
 */
class Settings extends Facade
{
    protected static $cached = false;

    protected static function getFacadeAccessor(): string
    {
        return PendingSettings::class;
    }
}
