<?php

declare(strict_types=1);

use Rawilk\Settings\Drivers\DatabaseDriver;
use Rawilk\Settings\Drivers\EloquentDriver;
use Rawilk\Settings\Drivers\Factory;
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Settings as SettingsService;
use Rawilk\Settings\Tests\Support\Drivers\CustomDriver;

it('can add custom drivers to the factory', function () {
    Settings::extend('my-custom-driver', fn () => new CustomDriver);

    expect(app(Factory::class)->driver('my-custom-driver'))->toBeInstanceOf(CustomDriver::class);
});

it('gets the default driver', function () {
    config()->set('settings.driver', 'eloquent');

    expect(Settings::driver())->toBeInstanceOf(EloquentDriver::class);
});

it('can get a specific driver', function () {
    Settings::extend('custom-driver', fn () => new CustomDriver);

    expect(Settings::driver('custom-driver'))->toBeInstanceOf(CustomDriver::class)
        ->and(Settings::driver('database'))->toBeInstanceOf(DatabaseDriver::class);
});

it('can temporarily switch drivers', function () {
    config()->set('settings.driver', 'eloquent');

    // This has to be done on a single instance (for testing like this),
    // since Settings is no longer a singleton in the package.
    $settings = settings();
    expect($settings->driver())->toBeInstanceOf(EloquentDriver::class);

    $settings->usingDriver('database', function (SettingsService $settings) {
        expect($settings->driver())->toBeInstanceOf(DatabaseDriver::class);
    });

    expect($settings->driver())->toBeInstanceOf(EloquentDriver::class);
});

it('restores the original driver even if an exception is thrown.', function () {
    config()->set('settings.driver', 'eloquent');

    // This has to be done on a single instance (for testing like this),
    // since Settings is no longer a singleton in the package.
    $settings = settings();
    expect($settings->driver())->toBeInstanceOf(EloquentDriver::class);

    try {
        $settings->usingDriver('database', function () {
            throw new RuntimeException('test');
        });
    } catch (Throwable) {
    }

    expect($settings->driver())->toBeInstanceOf(EloquentDriver::class);
});

test('usingDriver() accepts a driver instance', function () {
    config()->set('settings.driver', 'eloquent');

    // This has to be done on a single instance (for testing like this),
    // since Settings is no longer a singleton in the package.
    $settings = settings();

    $settings->usingDriver(new CustomDriver, function (SettingsService $settings) {
        expect($settings->driver())->toBeInstanceOf(CustomDriver::class);
    });

    expect($settings->driver())->toBeInstanceOf(EloquentDriver::class);
});
