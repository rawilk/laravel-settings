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

    public function generate(string $key, Context $context = null): string
    {
        $key = $this->normalizeKey($key);

        if ($context) {
            $key .= ':c:::' . $this->serializer->serialize($context);
        }

        return $key;
    }

    public function setContextSerializer(ContextSerializer $serializer): KeyGeneratorContract
    {
        $this->serializer = $serializer;

        return $this;
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
