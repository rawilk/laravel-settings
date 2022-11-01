---
title: Installation
sort: 3
---

## Installation

laravel-settings can be installed via composer:

```bash
composer require rawilk/laravel-settings
```

## Migrations

When using the `database` or `eloquent` drivers, you should publish the migration files. You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="settings-migrations"
php artisan migrate
```

## Configuration

You can publish the configuration file with:

```bash
php artisan vendor:publish --tag="settings-config"
```

You can view the default configuration here: https://github.com/rawilk/laravel-settings/blob/{branch}/config/breadcrumbs.php
