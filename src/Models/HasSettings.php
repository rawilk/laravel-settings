<?php

declare(strict_types=1);

namespace Rawilk\Settings\Models;

use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Settings;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator;

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

    protected static function bootHasSettings(): void
    {
        static::deleted(function (self $model) {
            if ($model->shouldFlushSettingsOnDelete()) {
                $model->settings()->flush();
            }
        });
    }

    /**
     * Additional arguments that uniquely identify this model.
     */
    protected function contextArguments(): array
    {
        return [];
    }

    protected function shouldFlushSettingsOnDelete(): bool
    {
        if (SettingsFacade::getKeyGenerator() instanceof Md5KeyGenerator) {
            return false;
        }

        return static::$flushSettingsOnDelete ?? true;
    }
}
