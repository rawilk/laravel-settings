<?php

declare(strict_types=1);

namespace Rawilk\Settings\Contracts;

use Rawilk\Settings\Support\Context;

interface KeyGenerator
{
    public function generate(string $key, ?Context $context = null): string;

    public function removeContextFromKey(string $key): string;

    public function setContextSerializer(ContextSerializer $serializer): self;

    public function contextPrefix(): string;
}
