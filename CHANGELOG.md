# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2026-07-10

### Added

- Support for `phpunit/phpunit:^12.0` and `phpunit/phpunit:^13.0`.
- The PHAR is now built and attached to every GitHub release.

### Changed

- Raised the minimum PHP version to 8.2 (dropped end-of-life PHP 8.1).
- Renamed the second parameter of `Stopwatch::stop()` from `$force` to `$silent`
  to match its behaviour (suppressing the exception when the timer was never
  started). Positional calls are unaffected; update any named-argument calls.

### Removed

- Support for `phpunit/phpunit:^10.0` (end of life).

### Fixed

- The average column now renders a dash (`-`) instead of `0.00` for a timer that
  was started but never stopped.

## [0.1.0]

- Initial release.

[Unreleased]: https://github.com/teqneers/phpunit-stopwatch/compare/0.2.0...HEAD
[0.2.0]: https://github.com/teqneers/phpunit-stopwatch/compare/0.1.0...0.2.0
[0.1.0]: https://github.com/teqneers/phpunit-stopwatch/releases/tag/0.1.0
