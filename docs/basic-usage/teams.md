---
title: Teams
sort: 2
---

## Introduction

The teams feature allows you to scope settings to a team or tenant in a multi-tenant application. By default, teams are disabled. This feature can be enabled through the config or even on the fly as needed.

## Enabling the Teams Feature

To enable teams, you must enable it in the settings config file:

```php
// config/settings.php
'teams' = true,
```

If you want to use a custom foreign key for teams, you can also set it in the config file:

```php
// config/settings.php
'team_foreign_key' => 'custom_team_id',
```

> {note} Be sure to run the [migration](/docs/laravel-settings/{version}/installation#user-content-migrations) from the package to add the necessary team column to your settings table.

## Working with Teams Settings

After implementing a solution for selecting a team on the authentication process (for example, setting the `team_id`
of the currently selected team on the **session:** `session(['team_id' => $team->id]);`), we can set the global `team_id`
from anywhere, but we recommend setting it in middleware.

Example Team Middleware:

```php
namespace App\Http\Middleware;

use Rawilk\Settings\Facades\Settings;

class TeamMiddleware
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            Settings::defaultTeam(session('team_id'));
        }

        // Other custom ways to get the team id
        /* if (! empty(auth('api')->user())) {
            // `getTeamIdFromToken()` example of custom method for getting the set team_id
            Settings::defaultTeam(
                auth('api')->user()->getTeamIdFromToken()
            );
        } */

        return $next($request);
    }
}
```

> {note} You must add your custom middleware to the `web` middleware group in `app/Http/Kernel.php`, or any other middleware groups
> that you want to use it in.

## Storing/Retrieving Team Settings

With the team id set on `Settings`, the service will automatically set the team id on any settings that are persisted or retrieved from the database.

## Changing Teams

There may be situations where you need to change the scoped team when interacting with settings. Even if you have a [global team set](#user-content-working-with-teams-settings), there are a few options for changing the team scope.

### Change Teams with a Callback

By providing a callback to `usingTeam()` on the settings service, you can scope everything inside the callback function to a specific team. Everything outside of your callback will revert to the original team value.

```php
use Rawilk\Settings\Facades\Settings;
use App\Models\Team;

$team = Team::first();

Settings::usingTeam($team, function () {
    Settings::set('team-setting', 'team value');
});
```

> {tip} `usingTeam()` accepts either a model instance or a string or integer value representing the id for your team.

### Change Teams Fluently

The `usingTeam()` method can be used without a callback as well if you'd prefer to just chain method calls instead of using a callback:

```php
use Rawilk\Settings\Facades\Settings;
use App\Models\Team;

$team = Team::first();

Settings::usingTeam($team)->set('team-setting', 'team value');
```

### Changing From a Team Scope to a Global Scope

If you have a [global team set](#user-content-working-with-teams-settings), you may have situations where you need to interact with settings that are not scoped to teams (i.e., the `team_id` column is `null`). The `noTeam()` method can be used to handle this:

```php
use Rawilk\Settings\Facades\Settings;

Settings::noTeam()->set('global-setting', 'global value');
```

> {tip} The `noTeam()` method accepts a callback function if you'd prefer to scope settings to a global scope that way.

## Contextual Settings

The `Context` object can be used with teams for further scoping of settings. The most common scenario for this would be if you have a
multi-tenant application, and you want to have user-specific settings for each tenant, you can use both teams and context.

Let's say the user has a timezone configured differently for each tenant. In tenant 1, the timezone is set to 'UTC' for the user, but in tenant 2
the timezone is set to 'America/Chicago' for the user. Here's how you can combine context and teams to get those different setting values.

```php
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Facades\Settings;

$userContext = new Context(['user_id' => 1]);

Settings::defaultTeam(1);

Settings::context($userContext)->get('timezone'); // UTC

Settings::usingTeam(2)->context($userContext)->get('timezone'); // America/Chicago
```

This will work with [model settings](/docs/laravel-settings/{version}/basic-usage/model-settings) as well. For more information on the
`Context` object, check out the [docs](/docs/laravel-settings/{version}/basic-usage/contextual-settings) here.
