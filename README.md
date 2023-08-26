# ymigval/laravel-indexnow

Laravel Package for notifying search engines about the latest changes to your URLs using the IndexNow API.

This package provides a simple mechanism to notify IndexNow about changes to your URLs. It's ideal for implementing in methods related to content creation, editing, or deletion on your website.


## What is IndexNow?

[IndexNow](https://www.indexnow.org) is a service that instantly informs search engines about the latest content changes on your website. This allows search engines to quickly update these changes in their search results.


## Installation

You can install the package via Composer:

```bash
composer require laravel-indexnow
```


Sending requests to IndexNow is currently disabled in the local environment. To enable request sending in any environment, set the "ignore_production_environment" property to true in the configuration file.

You can publish the configuration file using the following command:

```bash
php artisan vendor:publish --tag="indexnow"
```



## Usage
You can submit one or more pages per request by calling the facade and passing the URL(s) to the submit method.

### Submit a single page
```php
use Ymigval\LaravelIndexnow\Facade\IndexNow;

IndexNow::submit('https://example.com/cute-cats');
```

### Submit multiple pages
To submit multiple pages at once, provide an array of URLs.

```php
use Ymigval\LaravelIndexnow\Facade\IndexNow;

IndexNow::submit([
    'https://example.com/cute-cats',
    'https://example.com/dog-love',
    'https://example.com/nature',
]);

// Or using method chaining

IndexNow::setUrl('https://example.com/cute-cats')
        ->setUrl('https://example.com/dog-love')
        ->setUrl('https://example.com/nature')
        ->submit();
```


### IndexNow API Key

The IndexNow API requires a request key, which should match a key file within the host domain. Fortunately, this step is automated for you.

If you want to generate a new key and key file, use the following Artisan command:

```bash
php artisan indexnow:generate-key
```


### Preventing Spam
ymigval/laravel-indexnow includes spam prevention mechanisms. When potential spam is detected, the request sending is temporarily blocked for 24 hours.


### Logs
ymigval/laravel-indexnow includes an internal logging handler that you can use to print out actions' details.

Use the following Artisan command:

```bash
php artisan indexnow:logs
```


## Changelog
Please refer to the [CHANGELOG](CHANGELOG.md) for more information about recent changes.



## License
The MIT License (MIT). For more information, please see the [License File](LICENSE).