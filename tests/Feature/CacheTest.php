<?php

declare(strict_types=1);

use Rawilk\Settings\Contracts\ContextSerializer;
use Rawilk\Settings\Support\Context;

describe('cache enabled', function () {
    beforeEach(function () {
        config([
            'settings.cache' => true,
            'settings.cache_default_value' => true,
            'settings.encryption' => false,
            'settings.cache_key_prefix' => 'settings.',
        ]);

        $this->enableQueryLog();
    });

    it('caches values on retrieval', function () {
        $this->storeSetting('foo', 'bar');

        $this->resetQueryCount();
        expect(settings()->get('foo'))->toBe('bar')
            ->and(1)->toBeQueryCount();

        $this->resetQueryCount();
        expect(settings()->get('foo'))->toBe('bar')
            ->and(0)->toBeQueryCount();
    });

    it('refreshes the cache', function () {
        $this->storeSetting('foo', 'bar');

        settings()->get('foo');

        $this->resetQueryCount();

        settings()->get('foo');
        expect(0)->toBeQueryCount();

        settings()->set('foo', 'updated');
        $this->resetQueryCount();

        settings()->get('foo');
        expect(0)->toBeQueryCount()
            ->and(settings()->get('foo'))->toBe('updated');
    });

    test('boolean checks use the cache', function () {
        $this->storeSetting('truthy', true);
        $this->storeSetting('falsy', false);

        $this->resetQueryCount();

        settings()->isTrue('truthy');
        settings()->isFalse('falsy');

        expect(2)->toBeQueryCount();

        $this->resetQueryCount();

        settings()->isTrue('truthy');
        settings()->isFalse('falsy');

        expect(0)->toBeQueryCount();
    });

    it('will cache the default value if the setting is not persisted when retrieved', function () {
        config()->set('settings.cache_default_value', true);

        expect(settings()->get('foo', default: 'my default'))->toBe('my default')
            ->and(settings()->get('foo', default: 'new default'))->toBe('my default')
            ->and(1)->toBeQueryCount();
    });

    it('will not cache the default value if configured', function () {
        config()->set('settings.cache_default_value', false);

        expect(settings()->get('foo', default: 'my default'))->toBe('my default')
            ->and(settings()->get('foo', default: 'new default'))->toBe('new default')
            ->and(2)->toBeQueryCount();
    });

    it('will warm the cache for a setting when it is initially saved', function () {
        settings()->disableTeams();

        settings()->set('foo', 'bar');

        $this->resetQueryCount();

        expect(settings()->get('foo'))->toBe('bar')
            ->and(0)->toBeQueryCount()
            ->and(cache()->has('settings.foo'))->toBeTrue();
    });

    it('will warm the cache for a setting when it is updated', function () {
        settings()->disableTeams();

        settings()->set('foo', 'bar');
        settings()->get('foo');
        settings()->set('foo', 'updated');

        $this->resetQueryCount();

        expect(settings()->get('foo'))->toBe('updated')
            ->and(0)->toBeQueryCount()
            ->and(cache()->get('settings.foo'))->toBe(json_encode('updated'));
    });

    it('will disable the cache for a callback', function () {
        settings()->set('foo', 'bar');

        $this->resetQueryCount();

        settings()->get('foo');

        expect(0)->toBeQueryCount();

        $this->resetQueryCount();

        settings()->withoutCache(function () {
            settings()->get('foo');
        });

        expect(1)->toBeQueryCount();
    });

    it('will disable the cache for a callback without affecting previous state', function () {
        settings()->set('foo', 'bar');

        // 1. Initially, caching should be working.
        $this->resetQueryCount();
        settings()->get('foo');
        expect(0)->toBeQueryCount();

        // 2. Inside the callback, caching should be disabled (will trigger a query).
        settings()->withoutCache(function () {
            settings()->get('foo');
        });

        expect(1)->toBeQueryCount();

        // 3. After the callback, caching should be restored (no query).
        $this->resetQueryCount();
        settings()->get('foo');
        expect(0)->toBeQueryCount();
    });
});

describe('cache disabled', function () {
    beforeEach(function () {
        config([
            'settings.cache' => false,
            'settings.cache_default_value' => false,
        ]);

        $this->enableQueryLog();
    });

    it('does not use the cache if it is disabled when retrieving values', function () {
        $this->storeSetting('foo', 'bar');

        $this->resetQueryCount();

        settings()->get('foo');
        settings()->get('foo');

        expect(2)->toBeQueryCount();
    });

    it('will not warm the cache for a setting when it is saved if caching is disabled', function () {
        settings()->disableTeams();

        settings()->set('foo', 'bar');

        $this->resetQueryCount();

        settings()->get('foo');

        expect(1)->toBeQueryCount()
            ->and(cache()->has('settings.foo'))->toBeFalse();
    });
});

it('removes settings from the cache when they are deleted', function () {
    settings()->enableCache()->disableTeams();

    settings()->set('foo', 'bar');

    expect(cache()->has('settings.foo'))->toBeTrue();

    settings()->forget('foo');

    expect(cache()->has('settings.foo'))->toBeFalse();
});

describe('other', function () {
    beforeEach(function () {
        config([
            'settings.cache' => true,
            'settings.cache_default_value' => true,
            'settings.teams' => false,
        ]);
    });

    it('can generate a cache key for a given setting', function () {
        config()->set('settings.cache_key_prefix', 'my-prefix.');

        $contextSerializer = new class implements ContextSerializer
        {
            public function serialize(?Context $context = null): string
            {
                return 'serialized';
            }
        };

        /** @var Rawilk\Settings\Settings $settings */
        $settings = settings();

        $settings->getKeyGenerator()->setContextSerializer($contextSerializer);

        expect($settings->cacheKeyForSetting('foo'))->toBe('my-prefix.foo')
            ->and($settings->context(new Context(['foo' => 'bar']))->cacheKeyForSetting('foo'))->toBe('my-prefix.foo:c:::serialized');
    });

    it('can generate a cache key with teams enabled', function () {
        /** @var Rawilk\Settings\Settings $settings */
        $settings = settings();

        $settings
            ->defaultTeam(1)
            ->prefixCacheWith('my-prefix.')
            ->enableTeams();

        expect($settings->cacheKeyForSetting('foo'))->toBe('my-prefix.foo::team:1');
    });
});
