# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Support for `phpunit/phpunit:^12.0` and `phpunit/phpunit:^13.0`.

### Changed

- Raised the minimum PHP version to 8.2 (dropped end-of-life PHP 8.1).

### Removed

- Support for `phpunit/phpunit:^10.0` (end of life).

### Fixed

- The average column now renders a dash (`-`) instead of `0.00` for a timer that
  was started but never stopped.

## [0.1.0]

- Initial release.

[Unreleased]: https://github.com/teqneers/phpunit-stopwatch/compare/0.1.0...HEAD
[0.1.0]: https://github.com/teqneers/phpunit-stopwatch/releases/tag/0.1.0
