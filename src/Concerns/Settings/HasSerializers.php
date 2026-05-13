<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use Rawilk\Settings\Contracts\KeyGenerator;
use Rawilk\Settings\Contracts\ValueSerializer;

/**
 * @mixin \Rawilk\Settings\Settings
 */
trait HasSerializers
{
    public function getKeyGenerator(): KeyGenerator
    {
        return $this->keyGenerator;
    }

    public function setKeyGenerator(KeyGenerator $generator): static
    {
        $this->keyGenerator = $generator;

        return $this;
    }

    public function getValueSerializer(): ValueSerializer
    {
        return $this->valueSerializer;
    }

    public function setValueSerializer(ValueSerializer $serializer): static
    {
        $this->valueSerializer = $serializer;

        return $this;
    }
}
