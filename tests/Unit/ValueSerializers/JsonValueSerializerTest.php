<?php

declare(strict_types=1);

use Rawilk\Settings\Support\ValueSerializers\JsonValueSerializer;

beforeEach(function () {
    $this->serializer = new JsonValueSerializer;
});

it('serializes values as json', function (mixed $value, string $expected) {
    expect($this->serializer->serialize($value))->toBe($expected);
})->with([
    [1, '1'],
    ['1', '"1"'],
    [true, 'true'],
    [false, 'false'],
    [0, '0'],
    [null, 'null'],
    [1.1, '1.1'],
    [['array' => 'array'], '{"array":"array"}'],
    [[1, '2', 3], '[1,"2",3]'],
    [(object) ['a' => 'b'], '{"a":"b"}'],
]);

it('unserializes json values', function (string $value, mixed $expected) {
    expect($this->serializer->unserialize($value))->toBe($expected);
})->with([
    ['1', 1],
    ['"1"', '1'],
    ['true', true],
    ['false', false],
    ['0', 0],
    ['null', null],
    ['1.1', 1.1],
    ['{"array":"array"}', ['array' => 'array']],
    ['{"a":"b"}', ['a' => 'b']],
]);
