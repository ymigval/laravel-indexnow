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
    | Enable Submissions
    |--------------------------------------------------------------------------

    | Default: true
    |
    */
    'enable_submissions' => env('INDEXNOW_ENABLE_SUBMISSIONS', true),
];
