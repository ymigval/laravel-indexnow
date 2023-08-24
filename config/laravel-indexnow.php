<?php

// 'indexnow' => "api.indexnow.org",
// 'microsoft_bing' => "www.bing.com",
// 'naver' => "searchadvisor.naver.com",
// 'seznam' => "search.seznam.cz",
// 'yandex' => "yandex.com"

use Illuminate\Support\Facades\Config;

return [
    'searchengine'   => 'microsoft_bing',
    'enable_logging' => true,
    'production'     => Config::get('app.env', 'production'),
];
