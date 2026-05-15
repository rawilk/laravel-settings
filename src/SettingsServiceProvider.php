<?php

declare(strict_types=1);

namespace Rawilk\Settings;

use Rawilk\Settings\Drivers\Factory;
use Rawilk\Settings\Support\CacheStatus;
use Rawilk\Settings\Support\EncryptionStatus;
use Rawilk\Settings\Support\TeamResolver;
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

    public function registeringPackage(): void
    {
        $this->registerSettings();
    }

    public function provides(): array
    {
        return [
            Settings::class,
        ];
    }

    protected function registerSettings(): void
    {
        $this->app->scoped(Factory::class);

        $this->app->bind(Settings::class);

        $this->app->scoped(CacheStatus::class);

        $this->app->scoped(EncryptionStatus::class);

        $this->app->scoped(TeamResolver::class);
    }
}
