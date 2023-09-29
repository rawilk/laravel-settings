---
title: Events
sort: 5
---

## Introduction

New in v3, the settings service now fires events after certain operations. You may listen for these events in your application
to execute any additional code. Below are all the events this package dispatches.

## SettingsFlushed

The `SettingsFlushed` event is fired when `Settings::flush()` is called, and receives the keys that were flushed, if any, along
with the current team id and context object.

Here is the signature of the event:

```php
namespace Rawilk\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Rawilk\Settings\Support\Context;

final class SettingsFlushed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public bool|Collection|string $keys,
        public mixed $teamId,
        public bool|Context|null $context,
    ) {
    }
}
```

## SettingWasDeleted

The `SettingWasDeleted` event is fired anytime a single setting is deleted, using `Settings::forget()`. It receives the key that was deleted
along with the current team id and context object. The event will also receive the key that is used for storage, and the cache key for that setting.

Here is the signature of the event:

```php
namespace Rawilk\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Rawilk\Settings\Support\Context;

final class SettingWasDeleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public string $key,
        public string $storageKey,
        public string $cacheKey,
        public mixed $teamId,
        public bool|Context|null $context,
    ) {
    }
}
```

## SettingWasStored

The `SettingWasStored` event is fired when a setting is persisted to the database using `Settings::set()`. It will receive the key that was stored, the value, and the current team
id and context object. The storage key and cache key for that setting will also be provided to the event.

Here is the signature of the event:

```php
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
    ) {
    }
}
```

> {note} This event **will not be fired** if the setting exists and the value is not changed if you have caching enabled.
