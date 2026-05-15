<?php

declare(strict_types=1);

use Rawilk\Settings\Drivers\DatabaseDriver;
use Rawilk\Settings\Drivers\EloquentDriver;
use Rawilk\Settings\Drivers\Factory;
use Rawilk\Settings\Facades\Settings;
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
