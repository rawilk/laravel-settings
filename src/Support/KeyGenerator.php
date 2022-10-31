<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support;

class KeyGenerator
{
    public function __construct(protected ContextSerializer $serializer)
    {
    }

    public function generate(string $key, Context $context = null): string
    {
        return md5($key . $this->serializer->serialize($context));
    }
}
