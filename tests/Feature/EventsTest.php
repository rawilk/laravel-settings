<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Rawilk\Settings\Events\SettingsFlushed;
use Rawilk\Settings\Events\SettingWasDeleted;
use Rawilk\Settings\Events\SettingWasStored;
use Rawilk\Settings\Facades\Settings as SettingsFacade;

beforeEach(function () {
    Event::fake();
});

it('dispatches an event when settings are flushed', function () {
    settings()->flush();

    Event::assertDispatched(SettingsFlushed::class);
});

it('dispatches an event when a setting is deleted', function () {
    settings()->set('foo', 'bar');
    settings()->forget('foo');

    Event::assertDispatched(function (SettingWasDeleted $event) {
        expect($event->key)->toBe('foo')
            ->and($event->teamId)->toBeNull()
            ->and($event->context)->toBeNull();

        return true;
    });
});

it('dispatches an event when a setting is saved', function () {
    settings()->set('foo', 'bar');

    Event::assertDispatched(function (SettingWasStored $event) {
        expect($event->key)->toBe('foo')
            ->and($event->value)->toBe('bar');

        return true;
    });
});

it('does not dispatch SettingWasStored if the value does not change', function () {
    SettingsFacade::set('foo', 'bar');
    SettingsFacade::set('foo', 'bar');

    Event::assertDispatchedTimes(SettingWasStored::class, 1);
});

it('does not dispatch SettingWasStored if the value does not change with caching enabled', function () {
    settings()->enableCache();

    SettingsFacade::set('foo', 'bar');
    SettingsFacade::set('foo', 'bar');

    Event::assertDispatchedTimes(SettingWasStored::class, 1);
});
