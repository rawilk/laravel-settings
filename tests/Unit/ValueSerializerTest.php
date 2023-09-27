<?php

declare(strict_types=1);

use Rawilk\Settings\Support\ValueSerializer;

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
