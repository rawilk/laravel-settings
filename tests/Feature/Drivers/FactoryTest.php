<?php

declare(strict_types=1);

use Rawilk\Settings\Drivers\DatabaseDriver;
use Rawilk\Settings\Drivers\EloquentDriver;
use Rawilk\Settings\Drivers\Factory as DriverFactory;
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Tests\Support\Drivers\CustomDriver;

beforeEach(function () {
    config([
        'settings.driver' => 'eloquent',
    ]);
});

it('gets the default driver', function () {
    $driver = app(DriverFactory::class)->driver();

    expect($driver)->toBeInstanceOf(EloquentDriver::class);
});

it('throws an exception for unsupported drivers', function () {
    app(DriverFactory::class)->driver('foo');
})->throws(InvalidArgumentException::class);

it('can retrieve a known driver at runtime', function () {
    // Default driver
    expect(app(DriverFactory::class)->driver())->toBeInstanceOf(EloquentDriver::class)
        ->and(app(DriverFactory::class)->driver('database'))->toBeInstanceOf(DatabaseDriver::class);
});

test('custom drivers can be used', function () {
    app(DriverFactory::class)->extend('custom', function () {
        return new CustomDriver;
    });

    expect(app(DriverFactory::class)->driver('custom'))->toBeInstanceOf(CustomDriver::class);
});

test('a custom driver can be the default driver', function () {
    config()->set('settings.driver', 'custom_driver');

    app(DriverFactory::class)->extend('custom_driver', fn () => new CustomDriver);

    expect(app(DriverFactory::class)->driver())->toBeInstanceOf(CustomDriver::class)
        ->and(Settings::driver())->toBeInstanceOf(CustomDriver::class);
});
