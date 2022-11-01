<?php

declare(strict_types=1);

namespace Rawilk\Settings\Models;

use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Settings;
use Rawilk\Settings\Support\Context;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasSettings
{
    public function context(): Context
    {
        return new Context([
            'model' => static::class,
            'id' => $this->getKey(),
            ...$this->contextArguments(),
        ]);
    }

    public function settings(): Settings
    {
        return SettingsFacade::context($this->context());
    }

    /**
     * Additional arguments that uniquely identify this model.
     */
    protected function contextArguments(): array
    {
        return [];
    }
}
