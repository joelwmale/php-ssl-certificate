# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.0.0] - 2026-02-13

### Changed
- Minimum PHP version raised to 8.3
- All properties on `Certificate` are now `readonly`
- All source files use `declare(strict_types=1)`
- Typed properties and return types throughout
- Custom string helpers replaced with PHP 8 builtins
- CI matrix now tests PHP 8.3, 8.4, 8.5

### Fixed
- `ends_with()` calling undefined `length()` function (broke `coversDomain()`)
- `Url` constructor referencing non-existent `$this->url` property
- `Url` constructor calling non-existent static factory methods on `InvalidUrl`
- `idn_to_ascii()` applied to full URL instead of hostname only
- `containsDomain()` using `collect()` from dev-only `illuminate/collections`
- Unused `CouldNotDownloadCertificate` import in `Download`
- `composer test` script now correctly runs Pest instead of PHPUnit
- PHPUnit XML config updated for PHPUnit 11+ compatibility

### Removed
- `larapack/dd` dev dependency
- `illuminate/collections` dev dependency
- `ext-json` requirement (built-in since PHP 8.0)
- Custom `helpers.php` string functions (`str_contains`, `starts_with`, `ends_with`, `substr`)

## [3.0.1] - 2026-01-21

### Changed
- Fixes nesbot/carbon security vulnerability

## [3.0.0] - 2025-12-23

### Changed
- Dropped PHP 7x support
- Removed PHPUnit
- Added PestPHP
- Formatted tests

## [2.0.3] - 2025-12-23

### Fixed
- Update composer.json to support PHP 8.1 -> 8.5

## [2.0.2] - 2021-09-11

### Fixed
- Fix isValid() exception when called before Carbon dates are initialised

[2.0.2]: https://github.com/joelwmale/php-ssl-certificate/compare/1.0.1...2.0.2

## [2.0.1] - 2021-09-11

### Fixed
- Unit tests

## [2.0.0] - 2021-09-11

### Changed
- Added PHP 8.0 support

## [1.0.1] - 2019-08-23

### Added
- Dependency updates

### Fixed
- Failing tests

[1.0.1]: https://github.com/joelwmale/php-ssl-certificate/compare/1.0.0...1.0.1

## [1.0.0] - 2019-07-30

- Initial Release

[1.0.0]: https://github.com/joelwmale/php-ssl-certificate/compare/1.0.0...1.0.0
