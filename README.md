# Laravel IndexNow Package

A Laravel package that simplifies the integration with IndexNow API to notify search engines about URL changes in
real-time.

## 🎯 Overview

This package provides an elegant way to notify search engines about changes to your website's URLs using the IndexNow
API. Perfect for keeping search engines updated when content is created, modified, or deleted on your website.

## 🔍 What is IndexNow?

[IndexNow](https://www.indexnow.org) is an open protocol that enables websites to instantly inform search engines about
latest content changes. This ensures faster indexing and more up-to-date search results.

## Features

- 🚀 Instant search engine notification
- 📦 Simple integration with Laravel
- 🔄 Support for single and bulk URL submissions
- 🛡️ Built-in spam prevention
- 📝 Detailed logging system
- ⚙️ Configurable environment settings

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher

## ⚙️ Installation

Install the package via Composer:

```shell script
composer require ymigval/laravel-indexnow
```

## 🛠️ Configuration

1. Publish the configuration file:

```shell script
php artisan vendor:publish --tag="indexnow"
```

2. Generate an IndexNow API key from one of these services:
   - [Microsoft Bing](https://www.bing.com/indexnow/getstarted#implementation)
   - [NowIndex](https://www.nowindex.org/indexnow-key/)

3. Add your API key to the `.env` file:

```
INDEXNOW_API_KEY=your_api_key_here
```

### Key File Verification

Place your API key file at your domain's root with a `.txt` extension:

```
https://www.example.com/4cea016a4ddb408592569456a9c4896b.txt
```

Helpful commands:

```shell script
# View your IndexNow API key
php artisan indexnow:apikey

# If the route doesn't work, clear and cache routes
php artisan route:clear
php artisan route:cache

# Check IndexNow package status and configuration
php artisan indexnow:status
```

The `indexnow:status` command will help you verify:

- API Key configuration and validity
- Key file accessibility
- Configuration file status
- Search engine settings
- Submissions status
- Logging configuration
- Spam protection status
- Route registration
- Overall package status and provide recommendations

## 📋 Usage

### Single URL Submission
```php
use Ymigval\LaravelIndexnow\Facade\IndexNow;

IndexNow::submit('https://www.example.com/my-page');
```

### Multiple URLs Submission
```php
use Ymigval\LaravelIndexnow\Facade\IndexNow;

// Using array
IndexNow::submit([
    'https://www.example.com/page-1',
    'https://www.example.com/page-2',
    'https://www.example.com/page-3',
]);

// Or using method chaining
IndexNow::setUrl('https://www.example.com/page-1')
        ->setUrl('https://www.example.com/page-2')
        ->setUrl('https://www.example.com/page-3')
        ->submit();
```

## 🛡️ Security

### Spam Prevention

The package includes built-in spam protection that temporarily blocks requests for 24 hours when suspicious activity is
detected.

## 📊 Logging

View action logs using the Artisan command:

```shell script
php artisan indexnow:logs
```

## 📝 Changelog

See [CHANGELOG](CHANGELOG.md) for all notable changes.

## 📄 License

This package is open-source software licensed under the [MIT License](LICENSE).

## 🤝 Contributing

Contributions are welcome! Please see the [contributing guide](CONTRIBUTING.md) for details.

## ⭐ Support

If you find this package helpful, please consider giving it a star on GitHub!