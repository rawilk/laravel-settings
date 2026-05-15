<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Exceptions\InvalidKeyGenerator;
use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Settings;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializers\KeyValueContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\HashKeyGenerator;
use Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator;
use Rawilk\Settings\Tests\Support\Enums\BasicEnum;
use Rawilk\Settings\Tests\Support\Enums\IntBackedEnum;
use Rawilk\Settings\Tests\Support\Enums\SettingKey;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    config([
        'settings.cache' => false,
        'settings.encryption' => false,
        'settings.cache_default_value' => false,
    ]);
});

it('will return a new instance of PendingSettings through the facade', function () {
    $instance1 = SettingsFacade::prefixCacheWith('foo.');
    $instance2 = SettingsFacade::prefixCacheWith('bar.');

    expect($instance1)->not->toBe($instance2)
        ->and($instance1->getCachePrefix())->toBe('foo.')
        ->and($instance2->getCachePrefix())->toBe('bar.');
});

describe('has()', function () {
    it('can determine if a setting has been persisted', function () {
        expect(settings()->has('foo'))->toBeFalse();

        settings()->set('foo', 'bar');

        expect(settings()->has('foo'))->toBeTrue();

        DB::table('settings')->truncate();

        expect(settings()->has('foo'))->toBeFalse();
    });
});

describe('get()', function () {
    it('gets persisted setting values', function () {
        $this->storeSetting('foo', 'bar');

        expect(settings()->get('foo'))->toBe('bar');
    });

    it('returns a default value if a setting is not persisted', function () {
        expect(settings()->get('foo', 'default value'))->toBe('default value');
    });
});

describe('set()', function () {
    it('persists values', function () {
        settings()->set('foo', 'bar');

        assertDatabaseCount('settings', 1);

        expect(settings()->get('foo'))->toBe('bar');

        settings()->set('foo', 'updated value');

        assertDatabaseCount('settings', 1);

        expect(settings()->get('foo'))->toBe('updated value');
    });
});

describe('forget()', function () {
    it('removes persisted values from storage', function () {
        $this->storeSetting('foo', 'bar');
        $this->storeSetting('bar', 'foo');

        assertDatabaseCount('settings', 2);

        expect(settings()->has('foo'))->toBeTrue()
            ->and(settings()->has('bar'))->toBeTrue();

        settings()->forget('foo');

        assertDatabaseCount('settings', 1);

        expect(settings()->has('foo'))->toBeFalse()
            ->and(settings()->has('bar'))->toBeTrue();
    });
});

describe('boolean settings', function () {
    it('can evaluate stored boolean settings', function () {
        $this->storeSetting('app.debug', '1');
        expect(settings()->isTrue('app.debug'))->toBeTrue();

        $this->storeSetting('app.debug', '0');
        expect(settings()->isTrue('app.debug'))->toBeFalse()
            ->and(settings()->isFalse('app.debug'))->toBeTrue();

        $this->storeSetting('app.debug', true);
        expect(settings()->isTrue('app.debug'))->toBeTrue()
            ->and(settings()->isFalse('app.debug'))->toBeFalse();
    });
});

describe('all()', function () {
    it('can get all persisted values', function () {
        settings()->set('one', 'value 1');
        settings()->set('two', 'value 2');

        $storedSettings = settings()->all();

        expect($storedSettings)->toHaveCount(2)
            ->and($storedSettings[0]->key)->toBe('one')
            ->and($storedSettings[0]->original_key)->toBe('one')
            ->and($storedSettings[0]->value)->toBe('value 1')
            ->and($storedSettings[1]->key)->toBe('two')
            ->and($storedSettings[1]->original_key)->toBe('two')
            ->and($storedSettings[1]->value)->toBe('value 2');
    });

    it('can retrieve all settings for a given context', function () {
        $context = new Context(['id' => 'foo']);
        $contextTwo = new Context(['id' => 'foobar']);

        settings()->set('one', 'no context value');
        settings()->set('two', 'no context value 2');
        settings()->context($context)->set('one', 'context one value 1');
        settings()->context($context)->set('two', 'context one value 2');
        settings()->context($contextTwo)->set('one', 'context two value 1');

        $storedSettings = settings()->context($context)->all();

        expect($storedSettings)->toHaveCount(2)
            ->and($storedSettings[0]->key)->toBe('one')
            ->and($storedSettings[0]->original_key)->toBe('one:c:::id:foo')
            ->and($storedSettings[0]->value)->toBe('context one value 1')
            ->and($storedSettings[1]->key)->toBe('two')
            ->and($storedSettings[1]->original_key)->toBe('two:c:::id:foo')
            ->and($storedSettings[1]->value)->toBe('context one value 2');
    });

    it('throws an exception when doing a partial context lookup with unsupported key generators', function (string $unsupportedKeyGenerator) {
        $keyGenerator = app($unsupportedKeyGenerator);
        $keyGenerator->setContextSerializer(app(KeyValueContextSerializer::class));

        $settings = settings();

        $settings->setKeyGenerator($keyGenerator);

        $settings->context(new Context(['id' => 1]))->all();
    })->with([
        Md5KeyGenerator::class,
        HashKeyGenerator::class,
    ])->throws(InvalidKeyGenerator::class);
});

describe('flush()', function () {
    it('can flush all settings', function () {
        settings()->set('one', 'value 1');
        settings()->set('two', 'value 2');

        assertDatabaseCount('settings', 2);

        settings()->flush();

        assertDatabaseCount('settings', 0);
    });

    it('can flush a subset of settings', function () {
        settings()->set('one', 'value 1');
        settings()->set('two', 'value 2');
        settings()->set('three', 'value 3');

        assertDatabaseCount('settings', 3);

        settings()->flush(['one', 'three']);

        assertDatabaseCount('settings', 1);

        assertDatabaseMissing('settings', [
            'key' => 'one',
        ]);

        assertDatabaseMissing('settings', [
            'key' => 'three',
        ]);
    });

    it('can flush settings based on context', function () {
        $context = new Context(['id' => 'foo']);

        settings()->set('one', 'value 1');
        settings()->context($context)->set('one', 'context 1');

        assertDatabaseCount('settings', 2);

        settings()->context($context)->flush();

        assertDatabaseCount('settings', 1);

        assertDatabaseHas('settings', [
            'key' => 'one',
            'value' => json_encode('value 1'),
        ]);
    });
});

describe('context', function () {
    it('can retrieve values based on context', function () {
        $this->storeSetting('foo', 'bar');

        $userContext = new Context(['user_id' => 1]);
        $this->storeSetting('foo', 'user_1_value', context: $userContext);

        assertDatabaseCount('settings', 2);

        expect(settings()->get('foo'))->toBe('bar')
            ->and(settings()->context($userContext)->get('foo'))->toBe('user_1_value');
    });

    it('can determine if a setting is persisted based on context', function () {
        $this->storeSetting('foo', 'bar');

        $userContext = new Context(['user_id' => 1]);
        $user2Context = new Context(['user_id' => 2]);

        expect(settings()->has('foo'))->toBeTrue()
            ->and(settings()->context($userContext)->has('foo'))->toBeFalse();

        $this->storeSetting('foo', 'user 1 value', context: $userContext);

        expect(settings()->context($userContext)->has('foo'))->toBeTrue()
            ->and(settings()->context($user2Context)->has('foo'))->toBeFalse();

        $this->storeSetting('foo', 'user 2 value', context: $user2Context);

        expect(settings()->context($userContext)->has('foo'))->toBeTrue()
            ->and(settings()->context($user2Context)->has('foo'))->toBeTrue()
            ->and(settings()->has('foo'))->toBeTrue();
    });

    it('can remove persisted values based on context', function () {
        $userContext = new Context(['user_id' => 1]);
        $user2Context = new Context(['user_id' => 2]);

        $this->storeSetting('foo', 'bar');
        $this->storeSetting('foo', 'user 1 value', context: $userContext);
        $this->storeSetting('foo', 'user 2 value', context: $user2Context);

        expect(settings()->has('foo'))->toBeTrue()
            ->and(settings()->context($userContext)->has('foo'))->toBeTrue()
            ->and(settings()->context($user2Context)->has('foo'))->toBeTrue();

        settings()->context($user2Context)->forget('foo');

        expect(settings()->has('foo'))->toBeTrue()
            ->and(settings()->context($userContext)->has('foo'))->toBeTrue()
            ->and(settings()->context($user2Context)->has('foo'))->toBeFalse();
    });

    test('different contexts can be used at the same time - issue #88', function () {
        $context1 = new Context(['user_id' => 1]);
        $context2 = new Context(['user_id' => 2, 'team_id' => 1]);

        settings()->context($context2)->set('page_title', 'Default page title value');

        $value = SettingsFacade::context($context1)->get(
            'page_title',
            SettingsFacade::context($context2)->get('page_title')
        );

        expect($value)->toBe('Default page title value');
    });

    test('context can be scoped', function () {
        SettingsFacade::withContext(new Context(['user_id' => 'foo']), function (Settings $settings) {
            $settings->set('foo', 'bar');
        });

        assertDatabaseHas('settings', [
            'key' => 'foo:c:::user_id:foo',
            'value' => json_encode('bar'),
        ]);
    });

    test('previous context is restored after withContext() is finished', function () {
        $settings = settings();
        $settings->context(new Context(['user_id' => 1]));

        $settings->withContext(new Context(['user_id' => 2]), function () use ($settings) {
            $settings->set('foo', 'inner');
        });

        $settings->set('foo', 'outer');

        assertDatabaseHas('settings', [
            'key' => 'foo:c:::user_id:2',
            'value' => json_encode('inner'),
        ]);

        assertDatabaseHas('settings', [
            'key' => 'foo:c:::user_id:1',
            'value' => json_encode('outer'),
        ]);
    });

    test('previous context is restored after withContext() even when an exception is thrown', function () {
        $settings = settings();
        $settings->context(new Context(['user_id' => 1]));

        try {
            $settings->withContext(new Context(['user_id' => 2]), function () {
                throw new RuntimeException('test');
            });
        } catch (Throwable) {
        }

        $settings->set('foo', 'outer');

        assertDatabaseHas('settings', [
            'key' => 'foo:c:::user_id:1',
            'value' => json_encode('outer'),
        ]);
    });
});

describe('setting keys', function () {
    it('accepts an enum for a key instead of a string', function () {
        settings()->set(SettingKey::Timezone, 'foo');

        expect(settings()->get(SettingKey::Timezone))->toBe('foo')
            ->and(settings()->has(SettingKey::Timezone))->toBeTrue();

        settings()->forget(SettingKey::Timezone);

        expect(settings()->has(SettingKey::Timezone))->toBeFalse();
    });

    it('accepts int backed enums', function () {
        settings()->set(IntBackedEnum::Foo, 'bar');

        expect(settings()->get(IntBackedEnum::Foo))->toBe('bar');
    });

    it('accepts unit enums', function () {
        settings()->set(BasicEnum::Foo, 'bar');

        expect(settings()->get(BasicEnum::Foo))->toBe('bar');
    });
});
