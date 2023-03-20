<?php

namespace Rawilk\Settings\Support;

class KeyGenerator
{
    protected ContextSerializer $serializer;

    public function __construct(ContextSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function generate(string $key, Context $context = null): string
    {
        return md5($key.$this->serializer->serialize($context));
    }
}
