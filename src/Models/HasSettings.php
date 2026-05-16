<?php

declare(strict_types=1);

namespace Rawilk\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\KeyGenerators\HashKeyGenerator;
use Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator;
use Rawilk\Settings\Support\PendingSettings;

/**
 * @mixin Model
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

    public function settings(): PendingSettings
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
        $keyGenerator = SettingsFacade::getKeyGenerator();

        if ($keyGenerator instanceof Md5KeyGenerator || $keyGenerator instanceof HashKeyGenerator) {
            return false;
        }

        return static::$flushSettingsOnDelete ?? true;
    }
}
