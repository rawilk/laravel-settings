<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use Closure;
use Rawilk\Settings\Support\Context;

/**
 * @mixin \Rawilk\Settings\Settings
 */
trait HasContext
{
    protected null|Context|bool $context = null;

    /**
     * Pass in `false` for context when calling `all()` to only return results
     * that do not have context.
     */
    public function context(Context|bool|null $context = null): static
    {
        $this->context = $context;

        return $this;
    }

    public function withContext(Context|bool|null $context, Closure $callback): mixed
    {
        $previousContext = $this->context;
        $this->context($context);

        try {
            return $callback($this);
        } finally {
            $this->context($previousContext);
        }
    }

    public function getContext(): null|Context|bool
    {
        return $this->context;
    }
}
