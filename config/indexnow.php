<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Search Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Choose the preferred search engine for indexing:
    | Options: indexnow, microsoft_bing, naver, seznam, yandex
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
    | Enable or disable IndexNow logging.
    | Logs are automatically cleared after a certain period, retaining only the latest records.
    |
    | Default: Logging is enabled
    |
    */
    'enable_logging' => true,

    /*
    |--------------------------------------------------------------------------
    | Production Environment Control
    |--------------------------------------------------------------------------
    |
    | By default, in a local environment, requests to IndexNow will not be sent.
    | If you want to force sending requests in a local environment, disable this restriction by setting the property to true.
    |
    */
    'ignore_production_environment' => env('INDEXNOW_IGNORE_PRODUCTION_ENVIRONMENT', false),
];
