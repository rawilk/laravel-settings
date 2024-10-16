<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support\KeyGenerators;

use Illuminate\Support\Str;
use Rawilk\Settings\Contracts\ContextSerializer;
use Rawilk\Settings\Contracts\KeyGenerator as KeyGeneratorContract;
use Rawilk\Settings\Support\Context;

class ReadableKeyGenerator implements KeyGeneratorContract
{
    protected ContextSerializer $serializer;

    public function generate(string $key, ?Context $context = null): string
    {
        $key = $this->normalizeKey($key);

        if ($context) {
            $key .= $this->contextPrefix() . $this->serializer->serialize($context);
        }

        return $key;
    }

    public function removeContextFromKey(string $key): string
    {
        return Str::before($key, $this->contextPrefix());
    }

    public function setContextSerializer(ContextSerializer $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }

    public function contextPrefix(): string
    {
        return ':c:::';
    }

    protected function normalizeKey(string $key): string
    {
        // We want to preserve period characters in the key, however everything else is fair game
        // to convert to a slug.
        return Str::of($key)
            ->replace('.', '-dot-')
            ->slug()
            ->replace('-dot-', '.')
            ->__toString();
    }
}
