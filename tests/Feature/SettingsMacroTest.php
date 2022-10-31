<?php

declare(strict_types=1);

use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Settings;

beforeEach(function () {
    config([
        'settings.driver' => 'eloquent',
        'settings.table' => 'settings',
        'settings.cache' => false,
        'settings.encryption' => false,
    ]);
});

test('custom functions can be added to settings', function () {
    Settings::macro('myCustomFunction', function ($key) {
        $value = $this->get($key);

        return strtoupper($value);
    });

    SettingsFacade::set('foo', 'bar');

    expect(SettingsFacade::myCustomFunction('foo'))->toBe('BAR')
        ->and(SettingsFacade::myCustomFunction('foo'))->not()->toBe('bar')
        ->and(SettingsFacade::get('foo'))->toBe('bar');
});
