# Changelog

All notable changes to `laravel-settings` will be documented in this file

## v2.2.1 - 2023-02-07

### What's Changed

-   Bump dependabot/fetch-metadata from 1.3.5 to 1.3.6 by @dependabot in https://github.com/rawilk/laravel-settings/pull/13
-   Bump aglipanci/laravel-pint-action from 1.0.0 to 2.1.0 by @dependabot in https://github.com/rawilk/laravel-settings/pull/10
-   Improve internal handling of the Context object on Settings service class
-   Prevent decryption errors when checking if a value should be persisted or not on `set()`

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.2.0...v2.2.1

## v2.2.0 - 2022-12-07

### What's Changed

-   Allow cache to be temporarily disabled (via `temporarilyDisableCache()`)

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.1.1...v2.2.0

## v2.1.1 - 2022-12-06

### What's Changed

-   Bump dependabot/fetch-metadata from 1.3.4 to 1.3.5 by @dependabot in https://github.com/rawilk/laravel-settings/pull/8
-   Bump actions/checkout from 2 to 3 by @dependabot in https://github.com/rawilk/laravel-settings/pull/9
-   Prevent non-strings from being unserialized or decrypted

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.1.0...v2.1.1

## v2.1.0 - 2022-11-01

### Added

-   Feature: model settings by @rawilk in https://github.com/rawilk/laravel-settings/pull/7

### Changed

-   Composer: Update doctrine/dbal requirement from ^2.12 to ^3.5 by @dependabot in https://github.com/rawilk/laravel-settings/pull/5
-   Bump creyD/prettier_action from 3.0 to 4.2 by @dependabot in https://github.com/rawilk/laravel-settings/pull/6
-   Drop official PHP 8.0 support

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.0.1...v2.1.0

## 2.0.1 - 2022-02-23

### Updated

-   Add support for Laravel 9.\*
-   Add support for PHP 8.1

## 2.0.0 - 2020-12-01

### Breaking Changes

-   Drop support for Laravel v6 and v7
-   Drop support for php 7

### Updated

-   Add support for php 8
-   Update some of code base to use php 8 features

## 1.0.3 - 2020-10-26

### Fixed

-   Fix bug with context being reset when saving ([#3](https://github.com/rawilk/laravel-settings/issues/3))

## 1.0.2 - 2020-10-09

### Fixed

-   Wrap decrypting values in a try/catch to help prevent decryption errors when caching is used - [#2](https://github.com/rawilk/laravel-settings/issues/2)

## 1.0.1 - 2020-09-09

### Added

-   Add support for Laravel 8

## 1.0.0 - 2020-08-02

-   initial release
