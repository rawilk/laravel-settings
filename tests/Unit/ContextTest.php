<?php

declare(strict_types=1);

use Rawilk\Settings\Exceptions\InvalidContextValue;
use Rawilk\Settings\Support\Context;

it('serializes values when created', function () {
    $context = new Context(['test' => 'value', 'a' => 'b']);

    expect($context)->count()->toBe(2)
        ->and($context->get('test'))->toBe('value')
        ->and($context->get('a'))->toBe('b');
});

it('sets and removes context arguments', function () {
    $context = new Context;

    expect($context)->count()->toBe(0)
        ->and($context->has('test'))->toBeFalse();

    $context->set('test', 'a');

    expect($context)->count()->toBe(1)
        ->and($context->has('test'))->toBeTrue()
        ->and($context->get('test'))->toBe('a');

    $context->remove('test');

    expect($context)->count()->toBe(0)
        ->and($context->has('test'))->toBeFalse();
});

it('throws an exception for undefined arguments', function () {
    $context = new Context;
    $context->get('test');
})->throws(OutOfBoundsException::class);

it('can be converted to an array', function () {
    $context = new Context(['id' => 1, 'model' => 'User']);

    expect($context->toArray())
        ->toBeArray()
        ->toMatchArray([
            'id' => 1,
            'model' => 'User',
        ]);
});

it('only accepts numeric, string, or boolean values', function () {
    new Context([
        'id' => 1,
        'invalid-key' => ['array'],
    ]);
})->throws(InvalidContextValue::class, 'invalid-key');
