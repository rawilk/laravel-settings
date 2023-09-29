---
title: Model Settings
sort: 3
---

## Introduction

Starting with version `2.1.0`, models can easily have their own settings by using the `\Rawilk\Settings\Models\HasSettings` trait. This trait will automatically create a new `\Rawilk\Settings\Support\Context` object with properties that uniquely identify the model. See the [context](#user-content-context) section for more information.

## Usage

First, use the `HasSettings` trait in your Eloquent model.

```php
// ...
use Rawilk\Settings\Models\HasSettings;

class User extends Model
{
    use HasSettings;
}
```

Now whenever you need to interact with settings that are specific to that model, you can call `settings()`, which will return an instance of `\Rawilk\Settings\Settings`. This is essentially the same as calling `\Rawilk\Settings\Facades\Settings::context(...)`. This will allow you to do anything you could on the facade, but specifically for the model.

To store a setting:

```php
$user->settings()->set('foo', 'bar');
```

To retrieve a setting:

```php
$user->settings()->get('foo');
```

> {tip} You are able to specify a default value when retrieving a setting just like you can with the facade.

## Context

By default, when `context()` is called on a model, it will create a new `\Rawilk\Settings\Support\Context` object with the model's class and ID. If you need to override this behavior, you may override the `context` method on your model, but make sure you return a `Context` object. If you just need to add additional uniquely identifying properties, you may implement a `contextArguments` method on your model that returns an array of key/value pairs of data that is unique to a a model instance. These key/value pairs will be merged into the context object with your model's class and ID.

```php
protected function contextArguments(): array
{
    return [
        'email' => $this->email,
    ];
}
```

## Deleting Models

When a model is deleted, the trait registers an event listener for the model's `deleted` event, which will flush all settings for that model.
If you wish to disable this behavior, you can simply set a static boolean `$flushSettingsOnDelete` property on your model to `false`.

```php
namespace App\Models;

use Rawilk\Settings\Models\HasSettings;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasSettings;

    protected static bool $flushSettingsOnDelete = false;
}
```

If you are using soft-deletes on your model, you may need to disable this behavior as well, and manually flush the model's settings
when you force delete it.

> {note} This will only work when the `ReadableKeyGenerator` is used. 

For more information on the key generators, see [Custom Generators](/docs/laravel-settings/{version}/advanced-usage/custom-generators).
