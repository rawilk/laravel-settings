<?php

namespace Rawilk\Settings\Tests\Unit;

use OutOfBoundsException;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Tests\TestCase;

class ContextTest extends TestCase
{
    /** @test */
    public function it_serializes_values_when_created(): void
    {
        $context = new Context(['test' => 'value', 'a' => 'b']);

        self::assertCount(2, $context);
        self::assertEquals('value', $context->get('test'));
        self::assertEquals('b', $context->get('a'));
    }

    /** @test */
    public function it_sets_and_removes_context_arguments(): void
    {
        $context = new Context;

        self::assertCount(0, $context);
        self::assertFalse($context->has('test'));

        $context->set('test', 'a');

        self::assertCount(1, $context);
        self::assertTrue($context->has('test'));
        self::assertEquals('a', $context->get('test'));

        $context->remove('test');

        self::assertCount(0, $context);
        self::assertFalse($context->has('test'));
    }

    /** @test */
    public function it_throws_an_exception_for_undefined_arguments(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $context = new Context;
        $context->get('test');
    }
}
