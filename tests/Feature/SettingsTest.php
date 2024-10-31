<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Rawilk\Settings\Contracts\Setting;
use Rawilk\Settings\Drivers\EloquentDriver;
use Rawilk\Settings\Events\SettingsFlushed;
use Rawilk\Settings\Events\SettingWasDeleted;
use Rawilk\Settings\Events\SettingWasStored;
use Rawilk\Settings\Exceptions\InvalidEnumType;
use Rawilk\Settings\Exceptions\InvalidKeyGenerator;
use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializers\ContextSerializer;
use Rawilk\Settings\Support\ContextSerializers\DotNotationContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator;
use Rawilk\Settings\Support\KeyGenerators\ReadableKeyGenerator;
use Rawilk\Settings\Support\ValueSerializers\JsonValueSerializer;
use Rawilk\Settings\Tests\Support\Enums\IntBackedEnum as InvalidEnumTypeEnum;
use Rawilk\Settings\Tests\Support\Enums\SettingKey;

beforeEach(function () {
    config([
        'settings.driver' => 'database',
        'settings.table' => 'settings',
        'settings.cache' => false,
        'settings.encryption' => false,
        'settings.cache_default_value' => true,
    ]);

    setDatabaseDriverConnection();
});

it('can determine if a setting has been persisted', function () {
    expect(SettingsFacade::has('foo'))->toBeFalse();

    SettingsFacade::set('foo', 'bar');

    expect(SettingsFacade::has('foo'))->toBeTrue();

    DB::table('settings')->truncate();

    expect(SettingsFacade::has('foo'))->toBeFalse();
});

it('gets persisted setting values', function () {
    SettingsFacade::set('foo', 'bar');

    expect(SettingsFacade::get('foo'))->toBe('bar');
});

it('returns a default value if a setting is not persisted', function () {
    expect(SettingsFacade::get('foo', 'default value'))->toBe('default value');
});

it('can retrieve values based on context', function () {
    SettingsFacade::set('foo', 'bar');

    $userContext = new Context(['user_id' => 1]);
    SettingsFacade::context($userContext)->set('foo', 'user_1_value');

    expect(DB::table('settings')->count())->toBe(2)
        ->and(SettingsFacade::get('foo'))->toBe('bar')
        ->and(SettingsFacade::context($userContext)->get('foo'))->toBe('user_1_value');
});

it('can determine if a setting is persisted based on context', function () {
    SettingsFacade::set('foo', 'bar');

    $userContext = new Context(['user_id' => 1]);
    $user2Context = new Context(['user_id' => 2]);

    expect(SettingsFacade::has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($userContext)->has('foo'))->toBeFalse();

    SettingsFacade::context($userContext)->set('foo', 'user 1 value');

    expect(SettingsFacade::context($userContext)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($user2Context)->has('foo'))->toBeFalse();

    SettingsFacade::context($user2Context)->set('foo', 'user 2 value');

    expect(SettingsFacade::context($userContext)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($user2Context)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::has('foo'))->toBeTrue();
});

it('can remove persisted values based on context', function () {
    $userContext = new Context(['user_id' => 1]);
    $user2Context = new Context(['user_id' => 2]);
    SettingsFacade::set('foo', 'bar');
    SettingsFacade::context($userContext)->set('foo', 'user 1 value');
    SettingsFacade::context($user2Context)->set('foo', 'user 2 value');

    expect(SettingsFacade::has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($userContext)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($user2Context)->has('foo'))->toBeTrue();

    SettingsFacade::context($user2Context)->forget('foo');

    expect(SettingsFacade::has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($userContext)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($user2Context)->has('foo'))->toBeFalse();
});

it('persists values', function () {
    SettingsFacade::set('foo', 'bar');

    expect(DB::table('settings')->count())->toBe(1)
        ->and(SettingsFacade::get('foo'))->toBe('bar');

    SettingsFacade::set('foo', 'updated value');

    expect(DB::table('settings')->count())->toBe(1)
        ->and(SettingsFacade::get('foo'))->toBe('updated value');
});

it('removes persisted values from storage', function () {
    SettingsFacade::set('foo', 'bar');
    SettingsFacade::set('bar', 'foo');

    expect(DB::table('settings')->count())->toBe(2)
        ->and(SettingsFacade::has('foo'))->toBeTrue()
        ->and(SettingsFacade::has('bar'))->toBeTrue();

    SettingsFacade::forget('foo');

    expect(DB::table('settings')->count())->toBe(1)
        ->and(SettingsFacade::has('foo'))->toBeFalse()
        ->and(SettingsFacade::has('bar'))->toBeTrue();
});

it('can evaluate stored boolean settings', function () {
    SettingsFacade::set('app.debug', '1');
    expect(SettingsFacade::isTrue('app.debug'))->toBeTrue();

    SettingsFacade::set('app.debug', '0');
    expect(SettingsFacade::isTrue('app.debug'))->toBeFalse()
        ->and(SettingsFacade::isFalse('app.debug'))->toBeTrue();

    SettingsFacade::set('app.debug', true);
    expect(SettingsFacade::isTrue('app.debug'))->toBeTrue()
        ->and(SettingsFacade::isFalse('app.debug'))->toBeFalse();
});

it('can evaluate boolean stored settings using the json value serializer', function () {
    $settings = settings();
    (fn () => $this->valueSerializer = new JsonValueSerializer)->call($settings);

    $settings->set('app.debug', '1');
    expect($settings->isTrue('app.debug'))->toBeTrue();

    $settings->set('app.debug', '0');
    expect($settings->isTrue('app.debug'))->toBeFalse()
        ->and($settings->isFalse('app.debug'))->toBeTrue();

    $settings->set('app.debug', true);
    expect($settings->isTrue('app.debug'))->toBeTrue()
        ->and($settings->isFalse('app.debug'))->toBeFalse();
});

it('can cache values on retrieval', function () {
    enableSettingsCache();

    SettingsFacade::set('foo', 'bar');

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(1);

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(0);
});

it('flushes the cache when updating a value', function () {
    enableSettingsCache();

    SettingsFacade::set('foo', 'bar');

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(1);

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(0);

    SettingsFacade::set('foo', 'updated value');
    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('updated value');
    assertQueryCount(1);
});

it('does not invalidate other cached settings when updating a value', function () {
    enableSettingsCache();

    SettingsFacade::set('foo', 'bar');
    SettingsFacade::set('bar', 'foo');

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar')
        ->and(SettingsFacade::get('bar'))->toBe('foo');
    assertQueryCount(2);

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar')
        ->and(SettingsFacade::get('bar'))->toBe('foo');
    assertQueryCount(0);

    SettingsFacade::set('foo', 'updated value');
    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('updated value')
        ->and(SettingsFacade::get('bar'))->toBe('foo');
    assertQueryCount(1);
});

test('the boolean checks use cached values if cache is enabled', function () {
    enableSettingsCache();

    SettingsFacade::set('true.value', true);
    SettingsFacade::set('false.value', false);

    resetQueryCount();
    expect(SettingsFacade::isTrue('true.value'))->toBeTrue()
        ->and(SettingsFacade::isFalse('false.value'))->toBeTrue();
    assertQueryCount(2);

    resetQueryCount();
    expect(SettingsFacade::isTrue('true.value'))->toBeTrue()
        ->and(SettingsFacade::isFalse('false.value'))->toBeTrue();
    assertQueryCount(0);
});

it('does not use the cache if the cache is disabled', function () {
    SettingsFacade::disableCache();
    DB::enableQueryLog();

    SettingsFacade::set('foo', 'bar');

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(1);

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(1);
});

it('can encrypt values', function () {
    SettingsFacade::enableEncryption();

    SettingsFacade::set('foo', 'bar');

    $storedSetting = DB::table('settings')->first();
    $unEncrypted = unserialize(decrypt($storedSetting->value));

    expect($unEncrypted)->toBe('bar');
});

it('can decrypt values', function () {
    SettingsFacade::enableEncryption();

    SettingsFacade::set('foo', 'bar');

    // The stored value will be encrypted and not retrieve serialized yet if encryption
    // is enabled.
    $storedSetting = DB::table('settings')->first();
    expect(isSerialized($storedSetting->value))->toBeFalse()
        ->and(SettingsFacade::get('foo'))->toBe('bar');
});

it('does not encrypt if encryption is disabled', function () {
    SettingsFacade::disableEncryption();

    SettingsFacade::set('foo', 'bar');

    $storedSetting = DB::table('settings')->first();

    expect(isSerialized($storedSetting->value))->toBeTrue()
        ->and(unserialize($storedSetting->value))->toBe('bar');
});

it('does not try to decrypt if encryption is disabled', function () {
    SettingsFacade::enableEncryption();
    SettingsFacade::set('foo', 'bar');

    SettingsFacade::disableEncryption();

    $value = SettingsFacade::get('foo');

    expect($value)
        ->not->toBe('bar')
        ->not->toBe(serialize('bar'));
});

test('custom value serializers can be used', function () {
    $settings = settings();
    (fn () => $this->valueSerializer = new JsonValueSerializer)->call($settings);

    $settings->disableEncryption();

    $settings->set('foo', 'my value');
    $settings->set('array-value', ['foo' => 'bar', 'bool' => true]);

    $this->assertDatabaseHas('settings', [
        'value' => '"my value"',
    ]);

    $this->assertDatabaseHas('settings', [
        'value' => '{"foo":"bar","bool":true}',
    ]);

    expect($settings->get('foo'))->toBe('my value')
        ->and($settings->get('array-value'))->toBeArray()
        ->and($settings->get('array-value'))->toMatchArray(['foo' => 'bar', 'bool' => true]);
});

it('can get all persisted values', function () {
    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $settings->set('one', 'value 1');
    $settings->set('two', 'value 2');

    $storedSettings = $settings->all();

    expect($storedSettings)->toHaveCount(2)
        ->and($storedSettings[0]->key)->toBe('one')
        ->and($storedSettings[0]->original_key)->toBe('one')
        ->and($storedSettings[0]->value)->toBe('value 1')
        ->and($storedSettings[1]->key)->toBe('two')
        ->and($storedSettings[1]->original_key)->toBe('two')
        ->and($storedSettings[1]->value)->toBe('value 2');
});

test('retrieving all settings works with the Eloquent driver', function () {
    $settings = settings();
    (fn () => $this->driver = new EloquentDriver(app(Setting::class)))->call($settings);
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $settings->set('one', 'value 1');
    $settings->set('two', 'value 2');

    $storedSettings = $settings->all();

    expect($storedSettings)->toHaveCount(2)
        ->and($storedSettings[0]->key)->toBe('one')
        ->and($storedSettings[0]->original_key)->toBe('one')
        ->and($storedSettings[0]->value)->toBe('value 1')
        ->and($storedSettings[1]->key)->toBe('two')
        ->and($storedSettings[1]->original_key)->toBe('two')
        ->and($storedSettings[1]->value)->toBe('value 2');
});

it('can retrieve all settings for a given context', function () {
    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $context = new Context(['id' => 'foo']);
    $contextTwo = new Context(['id' => 'foobar']);

    $settings->set('one', 'no context value');
    $settings->set('two', 'no context value 2');
    $settings->context($context)->set('one', 'context one value 1');
    $settings->context($context)->set('two', 'context one value 2');
    $settings->context($contextTwo)->set('one', 'context two value 1');

    $storedSettings = $settings->context($context)->all();

    expect($storedSettings)->toHaveCount(2)
        ->and($storedSettings[0]->key)->toBe('one')
        ->and($storedSettings[0]->original_key)->toBe('one:c:::id:foo')
        ->and($storedSettings[0]->value)->toBe('context one value 1')
        ->and($storedSettings[1]->key)->toBe('two')
        ->and($storedSettings[1]->original_key)->toBe('two:c:::id:foo')
        ->and($storedSettings[1]->value)->toBe('context one value 2');
});

it('throws an exception when doing a partial context lookup using the md5 key generator', function () {
    $settings = settings();
    (fn () => $this->keyGenerator = (new Md5KeyGenerator)->setContextSerializer(new ContextSerializer))->call($settings);

    SettingsFacade::context(new Context(['id' => 1]))->all();
})->throws(InvalidKeyGenerator::class);

it('can flush all settings', function () {
    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $settings->set('one', 'value 1');
    $settings->set('two', 'value 2');

    $this->assertDatabaseCount('settings', 2);

    $settings->flush();

    $this->assertDatabaseCount('settings', 0);
});

it('can flush a subset of settings', function () {
    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $settings->set('one', 'value 1');
    $settings->set('two', 'value 2');
    $settings->set('three', 'value 3');

    $this->assertDatabaseCount('settings', 3);

    $settings->flush(['one', 'three']);

    $this->assertDatabaseCount('settings', 1);

    $this->assertDatabaseMissing('settings', [
        'key' => 'one',
    ]);

    $this->assertDatabaseMissing('settings', [
        'key' => 'three',
    ]);
});

it('can flush settings base on context', function () {
    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $context = new Context(['id' => 'foo']);

    $settings->set('one', 'value 1');
    $settings->context($context)->set('one', 'context 1');

    $this->assertDatabaseCount('settings', 2);

    $settings->context($context)->flush();

    $this->assertDatabaseCount('settings', 1);
});

it('dispatches an event when settings are flushed', function () {
    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    Event::fake();

    $settings->set('one', 'value 1');
    $settings->set('two', 'value 2');

    $settings->flush();

    Event::assertDispatched(SettingsFlushed::class);
});

it('dispatches an event when a setting is deleted', function () {
    Event::fake();

    SettingsFacade::set('foo', 'bar');
    SettingsFacade::forget('foo');

    Event::assertDispatched(function (SettingWasDeleted $event) {
        return $event->key === 'foo'
            && $event->morphId === false
            && $event->morphType === false
            && is_null($event->context);
    });
});

it('dispatches an event when a setting is saved', function () {
    Event::fake();

    SettingsFacade::set('foo', 'bar');

    Event::assertDispatched(function (SettingWasStored $event) {
        return $event->key === 'foo'
            && $event->value === 'bar';
    });
});

it('does not dispatch the stored event if the setting value has not changed', function () {
    Event::fake();

    // This only works when caching is enabled.
    SettingsFacade::enableCache();

    SettingsFacade::set('foo', 'bar');
    SettingsFacade::set('foo', 'bar');

    Event::assertDispatchedTimes(SettingWasStored::class, 1);
});

it('can generate the cache key for a given setting', function () {
    $settings = settings();
    $settings->useCacheKeyPrefix('settings.');
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    expect(SettingsFacade::cacheKeyForSetting('foo'))->toBe('settings.foo')
        ->and(SettingsFacade::context(new Context(['foo' => 'bar']))->cacheKeyForSetting('foo'))->toBe('settings.foo:c:::foo:bar');

    SettingsFacade::enableMorphs();
    SettingsFacade::setMorphs(1);

    expect(SettingsFacade::cacheKeyForSetting('foo'))->toBe('settings.foo::morphId:1:morphType:null');
});

it('accepts a backed enum for a key instead of a string', function () {
    SettingsFacade::set(SettingKey::Timezone, 'foo');

    expect(SettingsFacade::get(SettingKey::Timezone))->toBe('foo')
        ->and(SettingsFacade::has(SettingKey::Timezone))->toBeTrue();

    SettingsFacade::forget(SettingKey::Timezone);

    expect(SettingsFacade::has(SettingKey::Timezone))->toBeFalse();
});

it('throws an exception when an int backed enum is used', function () {
    SettingsFacade::get(InvalidEnumTypeEnum::Foo);
})->throws(InvalidEnumType::class);

test('if configured to not cache default values, it does not cache the default value if the requested setting is not persisted', function () {
    settings()->enableCache()->cacheDefaultValue(false);

    expect(settings()->get('foo', 'default value 1'))->toBe('default value 1')
        ->and(settings()->get('foo', 'default value 2'))->toBe('default value 2');
});

it('caches the default value if configured to', function () {
    settings()->enableCache()->cacheDefaultValue(true);

    expect(settings()->get('foo', 'default value 1'))->toBe('default value 1')
        ->and(settings()->get('foo', 'default value 2'))->toBe('default value 1');
});

// Helpers...

function assertQueryCount(int $expected): void
{
    test()->expect(DB::getQueryLog())->toHaveCount($expected);
}

function enableSettingsCache(): void
{
    SettingsFacade::enableCache();
    DB::connection()->enableQueryLog();
}

function resetQueryCount(): void
{
    DB::flushQueryLog();
}

/**
 * Determine if something is serialized. Based off a solution from WordPress.
 *
 * @see https://developer.wordpress.org/reference/functions/is_serialized/
 *
 * @param  string|mixed  $data
 */
function isSerialized(mixed $data): bool
{
    // If it isn't a string, it isn't serialized.
    if (! is_string($data)) {
        return false;
    }

    $data = trim($data);

    if ($data === 'N;') {
        return true;
    }

    if (strlen($data) < 4) {
        return false;
    }

    if ($data[1] !== ':') {
        return false;
    }

    $semiColon = strpos($data, ';');
    $brace = strpos($data, '}');

    // Either ; or } must exist.
    if ($semiColon === false && $brace === false) {
        return false;
    }

    // But neither must be in the first x characters.
    if ($semiColon !== false && $semiColon < 3) {
        return false;
    }

    if ($brace !== false && $brace < 4) {
        return false;
    }

    $token = $data[0];
    switch ($token) {
        case 's':
            if (strpos($data, '"') === false) {
                return false;
            }
            // Or else fall through
            // no break
        case 'a':
        case 'O':
            return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
        case 'b':
        case 'i':
        case 'd':
            $end = '';

            return (bool) preg_match("/^{$token}:[0-9.E+-]+;{$end}/", $data);
    }

    return false;
}
