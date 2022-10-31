<?php

declare(strict_types=1);

use Rawilk\Settings\Drivers\DatabaseDriver;
use Rawilk\Settings\Drivers\EloquentDriver;
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Tests\Support\Drivers\CustomDriver;

beforeEach(function () {
    config([
        'settings.driver' => 'eloquent',
    ]);
});

it('gets the default driver', function () {
    $driver = app('SettingsFactory')->driver();

    expect($driver)->toBeInstanceOf(EloquentDriver::class);
});

it('can set the default driver', function () {
    app('SettingsFactory')->setDefaultDriver('database');

    expect(app('SettingsFactory')->driver())->toBeInstanceOf(DatabaseDriver::class);
});

it('throws an exception for unsupported drivers', function () {
    app('SettingsFactory')->setDefaultDriver('unknown');

    app('SettingsFactory')->driver();
})->throws(InvalidArgumentException::class);

it('can retrieve a known driver at runtime', function () {
    // Default driver
    expect(app('SettingsFactory')->driver())->toBeInstanceOf(EloquentDriver::class)
        ->and(app('SettingsFactory')->driver('database'))->toBeInstanceOf(DatabaseDriver::class);
});

it("throws an exception if a driver's config is missing", function () {
    config([
        'settings.drivers.database' => null,
    ]);

    app('SettingsFactory')->driver('database');
})->expectException(InvalidArgumentException::class);

test('custom drivers can be used', function () {
    config([
        'settings.drivers.custom' => [
            'driver' => 'custom',
        ],
    ]);

    app('SettingsFactory')->extend('custom', function ($app, array $config) {
        return new CustomDriver;
    });

    expect(app('SettingsFactory')->driver('custom'))->toBeInstanceOf(CustomDriver::class);
});

test('a custom driver can be the default driver', function () {
    config([
        'settings.drivers.custom' => [
            'driver' => 'custom',
        ],
    ]);

    app('SettingsFactory')->extend('custom', function ($app, array $config) {
        return new CustomDriver;
    });

    app('SettingsFactory')->setDefaultDriver('custom');

    expect(app('SettingsFactory')->driver())->toBeInstanceOf(CustomDriver::class)
        ->and(Settings::getDriver())->toBeInstanceOf(CustomDriver::class);
});
