<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support\ContextSerializers;

use Rawilk\Settings\Contracts\ContextSerializer as ContextSerializerContract;
use Rawilk\Settings\Support\Context;

class DotNotationContextSerializer implements ContextSerializerContract
{
    public function serialize(?Context $context = null): string
    {
        if (is_null($context)) {
            return '';
        }

        return collect($context->toArray())
            ->map(function ($value, string $key) {
                // Use the model's morph class when possible.
                $value = match ($key) {
                    'model' => rescue(fn () => app($value)->getMorphClass(), $value),
                    default => $value,
                };

                if ($value === false) {
                    $value = 0;
                }

                return "{$key}:{$value}";
            })
            ->implode('::');
    }
}
