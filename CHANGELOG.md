# Changelog

All notable changes to `laravel-settings` will be documented in this file

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
