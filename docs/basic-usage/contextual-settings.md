---
title: Contextual Settings
sort: 2
---

If you need settings based on context, let's say for a specific user, you can do that by using the `Context` class.

```php
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Facades\Settings;

$userContext = new Context(['user_id' => 1]);
$user2Context = new Context(['user_id' => 2]);

Settings::context($userContext)->set('notifications', true);
Settings::context($user2Context)->set('notifications', false);

Settings::context($userContext)->isTrue('notifications'); // true
Settings::context($user2Context)->isTrue('notifications'); // false
```

> {tip} You can put anything you want in context, as long as it's in array form; however, the values must be numeric, strings, or booleans.

If you're looking to scope settings globally to a team or tenant, check out the [teams](/docs/laravel-settings/{version}/basic-usage/teams) documentation for more information. This can be much easier
than using the `Context` all the time for multi-tenant or team-based applications.

## Scoping Context

If you need to apply a context to several operations, you may find it better to do so within closure. We expose a `withContext()` method on settings to let you do this. 

Since `Settings` is not a singleton, you will need to use the same instance of settings for each operation in your closure. To help with this, we provide your callback function the instance of the settings service you set the context on:

```php
use Rawilk\Settings\Settings;
use Rawilk\Settings\Support\Context;

settings()->withContext(new Context(['user_id' => 1]), function (Settings $settings) {
    $settings->set('notifications', true);
    
    $settings->forget('some-other-context-setting');
});
```
