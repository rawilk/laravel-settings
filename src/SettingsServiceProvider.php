<?php

declare(strict_types=1);

namespace Rawilk\Settings;

use Rawilk\Settings\Contracts\Setting as SettingContract;
use Rawilk\Settings\Drivers\Factory;
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
            $settings = new Settings(
                $app['SettingsFactory']->driver()
            );

            $settings->useCacheKeyPrefix($app['config']['settings.cache_key_prefix'] ?? '');

            $settings->setCache($app['cache.store']);

            if (config('app.key')) {
                $settings->setEncrypter($app['encrypter']);
            }

            $app['config']['settings.cache'] ? $settings->enableCache() : $settings->disableCache();
            $app['config']['settings.encryption'] ? $settings->enableEncryption() : $settings->disableEncryption();
            $app['config']['settings.teams'] ? $settings->enableTeams() : $settings->disableTeams();

            return $settings;
        });
    }

    public function provides(): array
    {
        return [
            Settings::class,
            'SettingsFactory',
        ];
    }
}
