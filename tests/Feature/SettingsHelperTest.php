<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Settings;
use Rawilk\Settings\Support\Context;

beforeEach(function () {
    config([
        'settings.driver' => 'eloquent',
        'settings.table' => 'settings',
        'settings.cache' => false,
        'settings.encryption' => false,
    ]);
});

it('returns an instance of the settings class when no arguments are passed in', function () {
    expect(settings())->toBeInstanceOf(Settings::class);
});

it('sets values if an array is passed in as the first argument', function () {
    settings([
        'foo' => 'bar',
        'bar' => 'foo',
    ]);

    expect(DB::table('settings')->count())->toBe(2)
        ->and(SettingsFacade::get('foo'))->toBe('bar')
        ->and(SettingsFacade::get('bar'))->toBe('foo');
});

it('sets the context if a context is passed in as the third argument', function () {
    $context = new Context(['foo' => 'bar']);
    settings(['foo' => 'bar']);

    expect(SettingsFacade::context($context)->has('foo'))->toBeFalse();

    settings(['foo' => 'context value'], null, $context);

    expect(SettingsFacade::context($context)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::get('foo'))->toBe('bar')
        ->and(SettingsFacade::context($context)->get('foo'))->toBe('context value');
});

it('can retrieve values', function () {
    settings()->set('foo', 'bar');

    expect(settings('foo'))->toBe('bar')
        ->and(settings()->get('foo'))->toBe('bar');
});

it('returns a default value if a setting is not persisted', function () {
    expect(settings('foo', 'my default'))->toBe('my default');
});

it('returns values based on context', function () {
    $context = new Context(['foo' => 'bar']);

    settings()->set('foo', 'bar');
    settings()->context($context)->set('foo', 'context foo');

    expect(settings('foo'))->toBe('bar')
        ->and(settings('foo', null, $context))->toBe('context foo');
});
