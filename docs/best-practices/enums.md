---
title: Enums
sort: 4
---

## Introduction

PHP 8.1 introduced native enums, which can be a great way to define setting keys to use with this package.
Using an enum instead of hard-coding your setting keys can be helpful for both keeping track of which
settings are available to the application and for consistency.
As of version `3.1.0` of the package, the settings service will support enums as setting keys in the `get()`,
`set()`, `forget()`, and `has()` methods, as well as `isTrue()`, `isFalse()`, and `cacheKeyForSetting()`.

To use an enum as a setting key, you must use a string backed enum, like the example below:

```php
namespace App\Enums;

enum SettingKey: string
{
    case Timezone = 'app.timezone';
    case DefaultRole = 'default-role';
}
```

Now, you can use that enum when interacting with settings:

```php
settings()->get(SettingKey::Timezone);
```
