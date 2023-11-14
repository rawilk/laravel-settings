<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Rawilk\Settings\Support\ValueSerializers\ValueSerializer;

it('serializes values', function (mixed $value) {
    $serializer = new ValueSerializer;

    expect($serializer->serialize($value))->toBe(serialize($value));
})->with('values');

it('unserializes values', function (mixed $value) {
    $serializer = new ValueSerializer;

    $serialized = serialize($value);

    if (is_object($value)) {
        expect($serializer->unserialize($serialized))->toBeObject();
    } else {
        expect($serializer->unserialize($serialized))->toEqual($value);
    }
})->with('values');

test('certain objects can be safelisted for unserialization', function () {
    config(['settings.unserialize_safelist' => [
        Carbon::class,
    ]]);

    $serializer = new ValueSerializer;

    Date::setTestNow('2023-01-01 10:00:00');

    $now = Carbon::now();

    $serialized = serialize($now);
    $unserialized = $serializer->unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(Carbon::class)
        ->and($unserialized->eq($now))->toBeTrue()
        ->and($unserialized->toDateTimeString())->toBe('2023-01-01 10:00:00');
});

test('objects not in the safelist will be unserialized to __PHP_Incomplete_Class', function () {
    config(['settings.unserialize_safelist' => []]);

    $serializer = new ValueSerializer;

    $serialized = serialize(Carbon::now());
    $unserialized = $serializer->unserialize($serialized);

    expect($unserialized)->toBeObject()
        ->and($unserialized)->not->toBeInstanceOf(Carbon::class)
        ->and($unserialized)->toBeInstanceOf(__PHP_Incomplete_Class::class);
});

dataset('values', [
    null,
    1,
    1.1,
    true,
    false,
    'string',
    ['array' => 'array'],
    (object) ['a' => 'b'],
]);
