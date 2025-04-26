<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Search Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Specify your preferred search engine for URL indexing.
    | Each engine has its own specific requirements and characteristics.
    |
    | Available options:
    | - indexnow: General IndexNow protocol
    | - microsoft_bing: Bing search engine
    | - naver: Primary search engine in South Korea
    | - seznam: Popular search engine in Czech Republic
    | - yandex: Primary search engine in Russia
    |
    | Default: microsoft_bing
    |
    */

    'search_engine' => 'microsoft_bing',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the logging of IndexNow activities in the system logs.
    | When enabled, all indexing requests, responses, and errors are recorded
    | to facilitate diagnostics and monitoring.
    |
    | Logs are automatically pruned after a defined period to optimize storage space.
    |
    | Default: true (enabled)
    |
    */
    'enable_logging' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable Submissions
    |--------------------------------------------------------------------------
    |
    | Activates or deactivates the global submission of URLs to search engines.
    | This is a master switch that can be useful in development environments
    | or when you need to temporarily pause all indexing notifications.
    |
    | When disabled, no URLs will be submitted regardless of other settings or system events.
    |
    | Default: true (enabled)
    |
    */
    'enable_submissions' => env('INDEXNOW_ENABLE_SUBMISSIONS', true),

    /*
    |--------------------------------------------------------------------------
    | IndexNow API Key
    |--------------------------------------------------------------------------
    |
    | The API key required to authenticate requests to the IndexNow service.
    | This key is crucial for verifying ownership of your website and
    | must be unique to your domain.
    |
    | The key should:
    | - Be alphanumeric
    | - Be registered with the search engines you plan to use
    | - Be kept secure and not shared publicly
    |
    | It's recommended to store this key in your .env file
    |
    | Default: null
    |
    */
    'indexnow_api_key' => env('INDEXNOW_API_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Spam Protection Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the spam detection and temporary blocking of submissions
    | when rate limits are detected or other potential spam indicators.
    |
    | enable_spam_detection: When enabled, the system will automatically
    | block submissions temporarily after receiving certain HTTP error codes.
    |
    | spam_blocking_hours: How long (in hours) submissions should be blocked
    | after a potential spam or rate limit is detected.
    |
    | Default: enabled with 24 hour blocking period
    |
    */
    'enable_spam_detection' => env('INDEXNOW_ENABLE_SPAM_DETECTION', true),
    'spam_blocking_hours' => env('INDEXNOW_SPAM_BLOCKING_HOURS', 24),
];
