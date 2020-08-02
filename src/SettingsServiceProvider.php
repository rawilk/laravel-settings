<?php

namespace Rawilk\Settings;

use Illuminate\Support\ServiceProvider;
use Rawilk\Settings\Contracts\Setting as SettingContract;
use Rawilk\Settings\Drivers\Factory;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->registerModelBindings();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/settings.php', 'settings');

        $this->registerSettings();
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__ . '/../config/settings.php' => config_path('settings.php'),
        ], 'config');

        if (! class_exists('CreateSettingsTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_settings_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_settings_table.php'),
            ], 'migrations');
        }
    }

    protected function registerModelBindings(): void
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

        $this->app->singleton(Settings::class, static function ($app) {
            $settings = new Settings(
                $app['SettingsFactory']->driver()
            );

            $settings->setCache($app['cache.store']);

            if (config('app.key')) {
                $settings->setEncrypter($app['encrypter']);
            }

            $app['config']['settings.cache'] ? $settings->enableCache() : $settings->disableCache();
            $app['config']['settings.encryption'] ? $settings->enableEncryption() : $settings->disableEncryption();

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
