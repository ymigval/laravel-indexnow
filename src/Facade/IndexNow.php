<?php

namespace Ymigval\LaravelIndexnow\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Ymigval\LaravelIndexnow\IndexNowService setUrl(string $url)
 * @method static string|array submit(string|array|null $url = null)
 * @method static array getUrls()
 * @method static void setSearchEngine(string $searchengine)
 * @method static string getSearchEngine()
 * @method static string getKey()
 * @method static void keyFile(string $file)
 *
 * @see \Ymigval\LaravelIndexnow\IndexNowService
 */
class IndexNow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'IndexNow';
    }
}
