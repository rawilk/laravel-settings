<?php

declare(strict_types=1);

use Rawilk\Settings\Drivers\EloquentDriver;
use Rawilk\Settings\Models\Setting;

beforeEach(function () {
    $this->driver = new EloquentDriver(app(Setting::class));
    $this->model = app(Setting::class);
});

it('creates new entries', function () {
    $this->driver->set('foo', 'bar');

    expect($this->model::all())->count()->toBe(1)
        ->and($this->model::first()->value)->toBe('bar');
});

it('updates existing entries', function () {
    $this->driver->set('foo', 'bar');

    expect($this->model::first()->value)->toBe('bar');

    $this->driver->set('foo', 'updated value');

    expect($this->model::count())->toBe(1)
        ->and($this->model::first()->value)->toBe('updated value');
});

it('checks if a setting is persisted', function () {
    expect($this->driver->has('foo'))->toBeFalse();

    $this->driver->set('foo', 'bar');

    expect($this->driver->has('foo'))->toBeTrue();
});

it('gets a persisted setting value', function () {
    $this->driver->set('foo', 'bar');

    expect($this->driver->get('foo'))->toBe('bar');
});

it('returns a default value for settings that are not persisted', function () {
    expect($this->driver->get('foo', 'my default value'))->toBe('my default value');
});

it('removes persisted settings', function () {
    $this->driver->set('foo', 'bar');

    expect($this->driver->has('foo'))->toBeTrue();

    $this->driver->forget('foo');

    expect($this->driver->has('foo'))->toBeFalse();
});
