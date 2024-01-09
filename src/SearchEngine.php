<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Support\Str;

class SearchEngine
{
    /**
     * List of available search engine drivers.
     */
    private static $drivers = [
        'indexnow' => 'api.indexnow.org',
        'microsoft_bing' => 'www.bing.com',
        'naver' => 'searchadvisor.naver.com',
        'seznam' => 'search.seznam.cz',
        'yandex' => 'yandex.com',
    ];

    /**
     * Get the URL of a search engine driver.
     *
     * @return string|null
     */
    public static function getUrl(string $driver)
    {
        return self::$drivers[Str::lower($driver)] ?? null;
    }
}
