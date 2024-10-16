<?php

declare(strict_types=1);

namespace Rawilk\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Rawilk\Settings\Support\Context;

final class SettingWasStored
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public string $key,
        public string $storageKey,
        public string $cacheKey,
        public mixed $value,
        public mixed $teamId,
        public bool|Context|null $context,
    ) {}
}
