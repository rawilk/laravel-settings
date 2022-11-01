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
