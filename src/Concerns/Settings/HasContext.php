<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use Rawilk\Settings\Support\Context;

/**
 * @mixin \Rawilk\Settings\Settings
 */
trait HasContext
{
    protected null|Context|bool $context = null;

    // Instruct us to reset the context after a call (such as `get()`).
    // Meant for internal use only.
    protected bool $resetContext = true;

    /**
     * Pass in `false` for context when calling `all()` to only return results
     * that do not have context.
     */
    public function context(Context|bool|null $context = null): static
    {
        $this->context = $context;

        return $this;
    }

    protected function doNotResetContext(): static
    {
        $this->resetContext = false;

        return $this;
    }
}
