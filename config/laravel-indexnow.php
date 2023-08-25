<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Available Search Engines
    |--------------------------------------------------------------------------
    |
    | Options: indexnow, microsoft_bing, naver, seznam, yandex
    |
    | Default: microsoft_bing
    |
    */
   
    'searchengine'   => 'microsoft_bing',

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable IndexNow logging.
    | Logs are automatically cleared after a certain point, retaining only the latest records.
    |
    | Default: Logging is enabled
    |
    */
    'enable_logging' => true,

    /*
    |--------------------------------------------------------------------------
    | Control
    |--------------------------------------------------------------------------
    |
    | Sending requests to IndexNow is disabled by default.
    | To enable request sending, set the property to 'production'.
    |
    */
    'enable_request' => env('INDEXNOW_ENABLE', false)
];
