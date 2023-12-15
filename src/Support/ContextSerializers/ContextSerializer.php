<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support\ContextSerializers;

use Rawilk\Settings\Contracts\ContextSerializer as ContextSerializerContract;
use Rawilk\Settings\Support\Context;

class ContextSerializer implements ContextSerializerContract
{
    public function serialize(?Context $context = null): string
    {
        return serialize($context);
    }
}
