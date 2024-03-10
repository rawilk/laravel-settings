<?php

declare(strict_types=1);

namespace Rawilk\Settings\Contracts;

use Rawilk\Settings\Support\Context;

interface ContextSerializer
{
    public function serialize(?Context $context = null): string;
}
