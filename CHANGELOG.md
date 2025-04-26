# Changelog

All notable changes to `ymigval/laravel-indexnow` will be documented in this file.

## 3.0.0 - 2025-04-25

### Added

- Support for multiple search engines integration
- Improved URL validation and handling
- Enhanced performance and stability

### Changed

- Restructured core components for better maintainability
- Optimized request processing pipeline
- Updated dependencies for compatibility with latest frameworks

## 2.0.1 - 2024-04-01

### Fixed

- Avoid function declaration.

## 2.0.0 - 2024-01-05

### Changed

- Added support for the latest version of Laravel 11.
- Applied general optimizations.

## 1.0.2 - 2024-01-05

### Fixed

- Fixed an issue with the parseUrls() method.

## 1.1.0 - 2024-01-08

- You can host one to several text key files in other locations within the same host.
- Simplified internal key generator

## 1.0.1 - 2023-12-26

- Remove composer.lock file from the repository

The composer.lock file was deleted to ensure local dependencies can be managed flexibly without conflicts in different
environments. This allows developers to generate the file per their specific setups, accommodating changes in dependency
versions.

## 1.0.0 - 2023-08-25

- Initial release of `ymigval/laravel-indexnow`.
- Added functionality to notify IndexNow about changes in URLs.
- Facade method `submit` introduced to send URL notifications.
- Support for submitting single and multiple pages per request.
- Configuration option to ignore production environment for request sending.
- Added Artisan command for generating IndexNow API keys.
- Implemented spam prevention mechanism with 24-hour blocking.
- Included internal logging handler with an Artisan command for logs.
- Basic documentation covering installation and usage.

This version marks the initial launch of the `ymigval/laravel-indexnow` package, providing Laravel users with a convenient way to notify IndexNow about changes to their website URLs and enabling faster updates in search engine results.

