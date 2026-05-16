---
title: Team Resolver
sort: 5
---

## Introduction

The `TeamResolver` class handles resolving the current team to use for an operation. It is registered as a scoped binding (per request) in the container.

For most cases, you should use the `Settings` Facade instead of interacting with `TeamResolver` directly:

```php
use Rawilk\Settings\Facades\Settings;

// Scoped team
Settings::defaultTeam($team, function () {
    // all operations here will have the `$team` set as the team_id.
});

// Global team
Settings::defaultTeam($team);
```

## Advanced Usage

If you need lower-level control, resolve the `TeamResolver` from the container:

```php
use Rawilk\Settings\Support\TeamResolver;

// Custom resolution callback
app(TeamResolver::class)->resolveUsing(fn () => $teamId);
```

> {note} The `setTeam()` method on the resolver takes priority over `resolveUsing()`.

## Methods

### resolve

```php
public function resolve(): string|int|null
```

### resolveUsing

```php
public function resolveUsing(?Closure $callback): static
```

### setDefaultTeam

```php
public function setDefaultTeam(mixed $team): static
```

### withTeam

```php
public function withTeam(mixed $team, Closure $callback): mixed
```

### setTeam

```php
public function setTeam(mixed $team): static
```
