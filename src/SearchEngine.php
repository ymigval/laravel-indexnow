<?php

namespace Ymigval\LaravelIndexnow;

class SearchEngine
{
    /**
     * List of available search engine drivers.
     */
    private const SEARCH_ENGINE_DRIVERS = [
        'indexnow' => 'api.indexnow.org',
        'microsoft_bing' => 'www.bing.com',
        'naver' => 'searchadvisor.naver.com',
        'seznam' => 'search.seznam.cz',
        'yandex' => 'yandex.com',
    ];

    /**
     * Get the URL of a search engine driver.
     */
    public static function getDriverUrl(string $driver): ?string
    {
        return self::SEARCH_ENGINE_DRIVERS[strtolower($driver)] ?? null;
    }
}