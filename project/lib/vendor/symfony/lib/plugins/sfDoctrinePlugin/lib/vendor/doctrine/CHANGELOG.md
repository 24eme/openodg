# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
### Changed
- Changed packagist name to `friendsofsymfony1/doctrine1`
- Updated changelog to comply to [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) specification
### Deprecated
### Removed
- Remove support for PHP 5.2
### Fixed
- Cannot declare self-referencing constant 'Doctrine_Query::STATE_CLEAN' [PR-71](https://github.com/FriendsOfSymfony1/doctrine1/pull/71)
- Fix PHP 7.3 backward incompatible with continue statements [PR-67](https://github.com/FriendsOfSymfony1/doctrine1/pull/67)
- Remove `create_function` call as it is deprecated in 7.3 [PR-65](https://github.com/FriendsOfSymfony1/doctrine1/pull/65)
- Fix array to string conversion in Validator.php by @endelwar [PR-31](https://github.com/FriendsOfSymfony1/doctrine1/pull/31)
### Security
