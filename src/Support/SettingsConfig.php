<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Config;
use Rawilk\Settings\Contracts\ContextSerializer;
use Rawilk\Settings\Contracts\KeyGenerator;
use Rawilk\Settings\Contracts\Setting as SettingModelContract;
use Rawilk\Settings\Contracts\ValueSerializer;
use Rawilk\Settings\Exceptions\InvalidConfig;
use Rawilk\Settings\Models\Setting as SettingModel;
use Rawilk\Settings\Support\ContextSerializers\KeyValueContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\ReadableKeyGenerator;
use Rawilk\Settings\Support\ValueSerializers\JsonValueSerializer;

final class SettingsConfig
{
    public static function getModel(): SettingModelContract
    {
        $model = app(self::getModelClass());

        if (! ($model instanceof SettingModelContract)) {
            throw InvalidConfig::invalidEloquentModel($model::class);
        }

        return $model;
    }

    /**
     * @return class-string<SettingModelContract>
     */
    public static function getModelClass(): string
    {
        return config('settings.drivers.eloquent.model') ?? SettingModel::class;
    }

    public static function getDefaultDriver(): string
    {
        return config('settings.driver') ?? 'eloquent';
    }

    public static function getSettingsTable(): string
    {
        return config('settings.table') ?? 'settings';
    }

    public static function getDatabaseDriverConnection(): string
    {
        return config('settings.drivers.database.connection') ?? 'mysql';
    }

    public static function getTeamsForeignKey(): ?string
    {
        return config('settings.team_foreign_key') ?? 'team_id';
    }

    public static function getKeyGenerator(): KeyGenerator
    {
        $generatorClass = config(key: 'settings.key_generator', default: ReadableKeyGenerator::class);

        $generator = app($generatorClass);

        if (! ($generator instanceof KeyGenerator)) {
            throw InvalidConfig::invalidKeyGenerator($generatorClass);
        }

        $generator->setContextSerializer(self::getContextSerializer());

        return $generator;
    }

    public static function getValueSerializer(): ValueSerializer
    {
        $serializerClass = config(key: 'settings.value_serializer', default: JsonValueSerializer::class);

        $serializer = app($serializerClass);

        if (! ($serializer instanceof ValueSerializer)) {
            throw InvalidConfig::invalidValueSerializer($serializerClass);
        }

        return $serializer;
    }

    public static function getContextSerializer(): ContextSerializer
    {
        $serializerClass = config(key: 'settings.context_serializer', default: KeyValueContextSerializer::class);

        $serializer = app($serializerClass);

        if (! ($serializer instanceof ContextSerializer)) {
            throw InvalidConfig::invalidContextSerializer($serializerClass);
        }

        return $serializer;
    }

    public static function getCacheKeyPrefix(): string
    {
        return config('settings.cache_key_prefix') ?? '';
    }

    public static function shouldCacheDefaultValues(): bool
    {
        return Config::boolean('settings.cache_default_value', default: true);
    }

    public static function shouldCache(): bool
    {
        return Config::boolean('settings.cache', default: true);
    }

    /**
     * @return null|int|array{ 0: DateTimeInterface|DateInterval|int, 1: DateTimeInterface|DateInterval|int }|Closure|DateTimeInterface|DateInterval
     */
    public static function getCacheTtl(): null|int|array|Closure|DateTimeInterface|DateInterval
    {
        return config('settings.cache_ttl');
    }

    public static function shouldEncrypt(): bool
    {
        return Config::boolean('settings.encryption', default: true);
    }

    public static function teamsAreEnabled(): bool
    {
        return Config::boolean('settings.teams', default: false);
    }

    public static function getHashAlgorithm(): string
    {
        return config('settings.hash_algorithm', 'xxh128');
    }
}
