<?php

declare(strict_types=1);

namespace Rawilk\Settings;

use Rawilk\Settings\Contracts\Setting as SettingContract;
use Rawilk\Settings\Drivers\Factory;
use Rawilk\Settings\Support\ContextSerializers\ContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator;
use Rawilk\Settings\Support\ValueSerializers\ValueSerializer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SettingsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-settings')
            ->hasConfigFile()
            ->hasMigrations([
                'create_settings_table',
                'add_settings_team_field',
            ]);
    }

    public function packageBooted(): void
    {
        $this->bootModelBindings();
    }

    public function packageRegistered(): void
    {
        $this->registerSettings();
    }

    public function provides(): array
    {
        return [
            Settings::class,
            'SettingsFactory',
        ];
    }

    protected function bootModelBindings(): void
    {
        $config = $this->app['config']['settings.drivers.eloquent'];

        if (! $config) {
            return;
        }

        $this->app->bind(SettingContract::class, $config['model']);
    }

    protected function registerSettings(): void
    {
        $this->app->singleton(
            'SettingsFactory',
            fn ($app) => new Factory($app)
        );

        $this->app->singleton(Settings::class, function ($app) {
            $keyGenerator = $app->make($app['config']['settings.key_generator'] ?? Md5KeyGenerator::class);
            $keyGenerator->setContextSerializer(
                $app->make($app['config']['settings.context_serializer'] ?? ContextSerializer::class)
            );

            $settings = new Settings(
                driver: $app['SettingsFactory']->driver(),
                keyGenerator: $keyGenerator,
                valueSerializer: $app->make($app['config']['settings.value_serializer'] ?? ValueSerializer::class),
            );

            $settings->useCacheKeyPrefix($app['config']['settings.cache_key_prefix'] ?? '');

            $settings->setCache($app['cache.store']);

            $settings->cacheDefaultValue($app['config']['settings.cache_default_value'] ?? false);

            if (config('app.key')) {
                $settings->setEncrypter($app['encrypter']);
            }

            $app['config']['settings.cache'] ? $settings->enableCache() : $settings->disableCache();
            $app['config']['settings.encryption'] ? $settings->enableEncryption() : $settings->disableEncryption();
            $app['config']['settings.teams'] ? $settings->enableTeams() : $settings->disableTeams();

            $settings->setTeamForeignKey($app['config']['settings.team_foreign_key'] ?? 'team_id');

            return $settings;
        });
    }
}
