# Changelog

All notable changes to `ymigval/laravel-indexnow` will be documented in this file.

## 1.0 - 2023-08-25

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
