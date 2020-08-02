<?php

namespace Rawilk\Settings\Tests\Unit;

use Mockery;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializer;
use Rawilk\Settings\Support\KeyGenerator;
use Rawilk\Settings\Tests\TestCase;

class KeyGeneratorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /** @test */
    public function it_calls_serializer_when_generating_a_key(): void
    {
        $context = new Context;

        $serializer = $this->getContextSerializerMock();
        $serializer
            ->shouldReceive('serialize')
            ->with($context)
            ->andReturn('serialized');

        $generator = new KeyGenerator($serializer);

        self::assertEquals(md5('keyserialized'), $generator->generate('key', $context));
    }

    protected function getContextSerializerMock()
    {
        return Mockery::mock(ContextSerializer::class);
    }
}
