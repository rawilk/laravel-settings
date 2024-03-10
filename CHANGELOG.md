# Changelog

All notable changes to `laravel-settings` will be documented in this file

## v3.3.1 - 2024-03-10

### What's Changed

* Fix typo by @lakuapik in https://github.com/rawilk/laravel-settings/pull/51
* Bump aglipanci/laravel-pint-action from 2.3.0 to 2.3.1 by @dependabot in https://github.com/rawilk/laravel-settings/pull/52
* Added support for larvael 11.x by @demianottema in https://github.com/rawilk/laravel-settings/pull/58
* Bump ramsey/composer-install from 2 to 3 by @dependabot in https://github.com/rawilk/laravel-settings/pull/59
* Finish Laravel 11.x Compatibility Update by @rawilk in https://github.com/rawilk/laravel-settings/pull/60

### New Contributors

* @lakuapik made their first contribution in https://github.com/rawilk/laravel-settings/pull/51
* @demianottema made their first contribution in https://github.com/rawilk/laravel-settings/pull/58

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v3.3.0...v3.3.1

## v3.3.0 - 2023-11-14

### What's Changed

- Add safelist for object unserialization by @rawilk in https://github.com/rawilk/laravel-settings/pull/47
- Update docs by @rawilk in https://github.com/rawilk/laravel-settings/pull/48

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v3.2.0...v3.3.0

## v3.2.0 - 2023-11-01

### What's Changed

- Bump stefanzweifel/git-auto-commit-action from 4 to 5 by @dependabot in https://github.com/rawilk/laravel-settings/pull/42
- Add ability to set team id on a single call by @rawilk in https://github.com/rawilk/laravel-settings/pull/44
- v3.2.0 doc update by @rawilk in https://github.com/rawilk/laravel-settings/pull/45

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v3.1.2...v3.2.0

## v3.1.2 - 2023-10-07

### What's Changed

- Disable caching of the default value if configured that way by @rawilk in https://github.com/rawilk/laravel-settings/pull/40
- Update docs for new `cache_default_value` config option by @rawilk in https://github.com/rawilk/laravel-settings/pull/41

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v3.1.1...v3.1.2

## v3.1.1 - 2023-10-02

### What's Changed

- Give name `settings_key_team_id_unique` to unique index in team migration file - https://github.com/rawilk/laravel-settings/commit/1c44f29f6e8f78914876c629f1b0b3d1eff7f84c

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v3.1.0...v3.1.1

## v3.1.0 - 2023-10-01

### What's Changed

- Enums as setting keys by @rawilk in https://github.com/rawilk/laravel-settings/pull/37

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v3.0.0...v3.1.0

## v3.0.0 - 2023-09-29

### What's Changed

- Add team/multi-tenancy support by @rawilk in https://github.com/rawilk/laravel-settings/pull/28
- Remove Laravel 8.x support
- Remove Laravel 9.x support
- Add PHP 8.3 support
- Update interface method signatures for `Driver` and `Setting`
- Add support for custom key and context serializers by @rawilk in https://github.com/rawilk/laravel-settings/pull/30
- Add custom value serializer support by @rawilk in https://github.com/rawilk/laravel-settings/pull/31
- Fix typo in `isFalse` check - https://github.com/rawilk/laravel-settings/pull/34/commits/84989d4803e9ee99cfbd84925f8dae783c81b55e
- Add support to fetch/flush all settings by @rawilk in https://github.com/rawilk/laravel-settings/pull/32
- Dispatch events for certain operations in settings service @rawilk in https://github.com/rawilk/laravel-settings/pull/33
- Add ability to get the cache key for a given setting key - https://github.com/rawilk/laravel-settings/pull/34/commits/91fbd874f653b50f37f092379ee997bf2642e368

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.2.2...v3.0.0

## v2.2.2 - 2023-03-20

### What's Changed

- Bump creyD/prettier_action from 4.2 to 4.3 by @dependabot in https://github.com/rawilk/laravel-settings/pull/15
- Bump aglipanci/laravel-pint-action from 2.1.0 to 2.2.0 by @dependabot in https://github.com/rawilk/laravel-settings/pull/17
- Add Laravel 10.x Support by @rawilk in https://github.com/rawilk/laravel-settings/pull/18
- Add Php 8.2 compatibility by @rawilk in https://github.com/rawilk/laravel-settings/pull/19

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.2.1...v2.2.2

## v2.2.1 - 2023-02-07

### What's Changed

- Bump dependabot/fetch-metadata from 1.3.5 to 1.3.6 by @dependabot in https://github.com/rawilk/laravel-settings/pull/13
- Bump aglipanci/laravel-pint-action from 1.0.0 to 2.1.0 by @dependabot in https://github.com/rawilk/laravel-settings/pull/10
- Improve internal handling of the Context object on Settings service class
- Prevent decryption errors when checking if a value should be persisted or not on `set()`

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.2.0...v2.2.1

## v2.2.0 - 2022-12-07

### What's Changed

- Allow cache to be temporarily disabled (via `temporarilyDisableCache()`)

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.1.1...v2.2.0

## v2.1.1 - 2022-12-06

### What's Changed

- Bump dependabot/fetch-metadata from 1.3.4 to 1.3.5 by @dependabot in https://github.com/rawilk/laravel-settings/pull/8
- Bump actions/checkout from 2 to 3 by @dependabot in https://github.com/rawilk/laravel-settings/pull/9
- Prevent non-strings from being unserialized or decrypted

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.1.0...v2.1.1

## v2.1.0 - 2022-11-01

### Added

- Feature: model settings by @rawilk in https://github.com/rawilk/laravel-settings/pull/7

### Changed

- Composer: Update doctrine/dbal requirement from ^2.12 to ^3.5 by @dependabot in https://github.com/rawilk/laravel-settings/pull/5
- Bump creyD/prettier_action from 3.0 to 4.2 by @dependabot in https://github.com/rawilk/laravel-settings/pull/6
- Drop official PHP 8.0 support

**Full Changelog**: https://github.com/rawilk/laravel-settings/compare/v2.0.1...v2.1.0

## 2.0.1 - 2022-02-23

### Updated

- Add support for Laravel 9.*
- Add support for PHP 8.1

## 2.0.0 - 2020-12-01

### Breaking Changes

- Drop support for Laravel v6 and v7
- Drop support for php 7

### Updated

- Add support for php 8
- Update some of code base to use php 8 features

## 1.0.3 - 2020-10-26

### Fixed

- Fix bug with context being reset when saving ([#3](https://github.com/rawilk/laravel-settings/issues/3))

## 1.0.2 - 2020-10-09

### Fixed

- Wrap decrypting values in a try/catch to help prevent decryption errors when caching is used - [#2](https://github.com/rawilk/laravel-settings/issues/2)

## 1.0.1 - 2020-09-09

### Added

- Add support for Laravel 8

## 1.0.0 - 2020-08-02

- initial release
