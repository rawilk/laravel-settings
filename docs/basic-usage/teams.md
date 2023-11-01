---
title: Teams
sort: 2
---

## Introduction

As of v3, `laravel-settings` supports using teams or multi-tenancy. This means you can have settings scoped to a team, and retrieve them by team.
By default, teams are disabled, however you can easily enable them by setting the `teams` config option to `true`.

## Enabling the Teams Feature

> {info} These configuration changes must be made **before** running the migrations when first installing the package or when upgrading from v2.
> <br><br>
> If you have already run the migrations and want to upgrade your implementation, you can add a migration and copy the contents of the `add_settings_team_field` migration from the package
> after you make the configuration changes below.

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

## Working with Teams Settings

After implementing a solution for selecting a team on the authentication process (for example, setting the `team_id`
of the currently selected team on the **session:** `session(['team_id' => $team->id]);`), we can set the global `team_id`
from anywhere, but we recommend setting it in a middleware.

Example Team Middleware:

```php
namespace App\Http\Middleware;

use Rawilk\Settings\Facades\Settings;

class TeamMiddleware
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            Settings::setTeamId(session('team_id'));
        }

        // Other custom ways to get the team id
        /* if (! empty(auth('api')->user())) {
            // `getTeamIdFromToken()` example of custom method for getting the set team_id
            Settings::setTeamId(
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

## Changing the Active Team ID

While your middleware may set the current team id for settings, you may need to change it later to another team for various reasons. The two most common
reasons are:

### Switching Teams After Login

If your application allows the user to switch between various teams which they belong to, you can activate the team id for settings by calling the `setTeamId` method:

```php
Settings::setTeamId($teamId)->get(...);
```

### Administering Team Details

You may have created a user management page where you can view the settings of users on certain teams. For managing that user
in each team they belong to, you must use the `setTeamId` method on `Settings` to cause settings to be scoped
for that specific team.

If you need to switch back to the current team id, you can use the `getTeamId` method on `Settings`.

```php
$currentTeamId = Settings::getTeamId();

Settings::setTeamId($teamId)->set(...);

// Revert back to original team id of request.
Settings::setTeamId($currentTeamId);
```

> {tip} You can pass in an eloquent model to `setTeamId` instead of an id if you prefer.

#### Temporary Team Switching

As of `v3.2.0`, to make administering team settings easier, we've added the following convenience methods to `Settings`:

- `usingTeam($teamId)`: Use this method to scope settings to a specific team on a single call.
- `withoutTeams()`: Use this method to remove team scoping for a single call.

```php
settings()->usingTeam('my-team-id')->set('foo', 'bar');
```

## Contextual Settings

The `Context` object can be used in conjunction with teams for further scoping of settings. The most common scenario for this would be if you have a
multi-tenant application, and you want to have user-specific settings for each tenant, you can use both teams and context.

Let's say the user has a timezone configured differently for each tenant. In tenant 1, the timezone is set to 'UTC' for the user, but in tenant 2
the timezone is set to 'America/Chicago' for the user. Here's how you can combine context and teams to get those different setting values.

```php
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Facades\Settings;

$userContext = new Context(['user_id' => 1]);

Settings::setTeamId(1);

Settings::context($userContext)->get('timezone'); // UTC

Settings::setTeamId(2);

Settings::contest($userContext)->get('timezone'); // America/Chicago
```

This will also work with [model settings](/docs/laravel-settings/{version}/basic-usage/model-settings) as well. For more information on the
`Context` object, check out the [docs](/docs/laravel-settings/{version}/basic-usage/contextual-settings) here.
