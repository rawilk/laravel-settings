<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support\KeyGenerators;

use Rawilk\Settings\Contracts\ContextSerializer;
use Rawilk\Settings\Contracts\KeyGenerator as KeyGeneratorContract;
use Rawilk\Settings\Support\Context;
use RuntimeException;

class Md5KeyGenerator implements KeyGeneratorContract
{
    protected ContextSerializer $serializer;

    public function generate(string $key, ?Context $context = null): string
    {
        return md5($key . $this->serializer->serialize($context));
    }

    public function removeContextFromKey(string $key): string
    {
        throw new RuntimeException('Md5KeyGenerator does not support removing the context from the key.');
    }

    public function setContextSerializer(ContextSerializer $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }

    public function contextPrefix(): string
    {
        throw new RuntimeException('Md5KeyGenerator does not support a context prefix.');
    }
}
