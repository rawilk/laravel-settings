<?php

declare(strict_types=1);

use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializers\DotNotationContextSerializer;
use Rawilk\Settings\Tests\Support\Models\User;

beforeEach(function () {
    $this->serializer = new DotNotationContextSerializer;
});

it('serializes a context object to dot notation', function () {
    $context = new Context([
        'model' => User::class,
        'id' => 1,
        'bool_value' => true,
    ]);

    expect($this->serializer->serialize($context))->toBe('model:user::id:1::bool_value:1');
});

it('serializes null values to an empty string', function () {
    expect($this->serializer->serialize(null))->toBe('');
});

it('handles false boolean values', function () {
    $context = new Context([
        'bool-value' => false,
    ]);

    expect($this->serializer->serialize($context))->toBe('bool-value:0');
});
