<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Drivers\DatabaseDriver;

beforeEach(function () {
    $this->driver = new DatabaseDriver(app('db')->connection(), 'settings');
    $this->db = DB::table('settings');
});

it('creates new entries', function () {
    $this->driver->set('foo', 'bar');

    expect($this->db->count())->toBe(1)
        ->and($this->db->where('key', 'foo')->value('value'))->toBe('bar');
});

it('updates existing values', function () {
    $this->driver->set('foo', 'bar');

    expect($this->db->where('key', 'foo')->value('value'))->toBe('bar');

    $this->driver->set('foo', 'updated value');

    expect($this->db->count())->toBe(1)
        ->and($this->db->where('key', 'foo')->value('value'))->toBe('updated value');
});

it('checks if a setting is persisted', function () {
    expect($this->driver->has('foo'))->toBeFalse();

    $this->driver->set('foo', 'bar');

    expect($this->driver->has('foo'))->toBeTrue();
});

it('gets a persisted setting value', function () {
    $this->driver->set('foo', 'some value');

    expect($this->driver->get('foo'))->toBe('some value');
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
