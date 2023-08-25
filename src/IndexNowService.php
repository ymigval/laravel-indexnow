<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Exceptions\ExcessUrlsException;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\Exceptions\SearchEngineUnknownException;

class IndexNowService
{
    /**
     * @var Search Engine
     */
    private $searchengine;

    /**
     * @var array
     */
    private $urls = [];

    /**
     * Initialize the IndexNow service.
     *
     * @param string $searchengine The Search Engine to be used for indexing.
     */
    public function __construct(string $searchengine)
    {
        $this->setSearchEngine($searchengine);
    }

    /**
     * Set the Search Engine for the IndexNow service.
     *
     * @param string $searchengine
     * @return void
     * @throws SearchEngineUnknownException
     */
    public function setSearchEngine(string $searchengine): void
    {
        $this->searchengine = SearchEngine::getUrl($searchengine);

        if (is_null($this->searchengine)) {
            throw new SearchEngineUnknownException();
        }
    }

    /**
     * Get the established searchengine for the IndexNow service.
     *
     * @return string
     */
    public function getSearchEngine(): string
    {
        return (string) $this->searchengine;
    }

    /**
     * Set URLs for indexing.
     *
     * @param  string|array $url
     * @return $this
     */
    public function setUrls($url): IndexNowService
    {
        if (is_array($url)) {
            $this->urls = array_merge($this->urls, $url);
        } else {
            $this->urls[] = $url;
        }

        return $this;
    }

    /**
     * Get URLs to be indexed.
     *
     * @return array
     */
    public function getUrls(): array
    {
        return array_values(array_unique($this->urls));
    }

    /**
     * Get the IndexNow API key.
     *
     * @return string
     * @throws KeyFileDoesNotExistException | InvalidKeyException
     */
    public function getKey(): string
    {
        return IndexNowApiKeyManager::getApiKey();
    }

    /**
     * Submit one or more URLs for indexing.
     *
     * @param  string|array|null $url
     * @return string|array
     */
    public function submit($url = null)
    {
        if (!is_null($url)) {
            $this->setUrls($url);
        }

        $process = $this->process();

        LogManager::addLog($process);

        return $process;
    }

    /**
     * Parse and prepare URLs for indexing.
     *
     * @return void
     * @throws ExcessUrlsException
     */
    private function parseUrls(): void
    {
        $urlApp = Str::of(Config::get('app.urls'))
            ->replaceMatches('#/*$#', '');

        if (count($this->urls) > 10000) {
            throw new ExcessUrlsException();
        }

        $this->urls = array_map(function ($url) use ($urlApp) {
            if (is_null(parse_url($url, PHP_URL_HOST))) {
                $url = Str::of($urlApp)->append($url);
            }

            return $url;
        }, $this->urls);

        if (count($this->urls) == 1) {
            $this->urls[0] = rawurlencode($this->urls[0]);
        }
    }

    /**
     * Get the host for the URLs.
     *
     * @return string
     */
    private function getHost(): string
    {
        if (isset($this->getUrls()[0])) {
            $host = parse_url(rawurldecode($this->getUrls()[0]), PHP_URL_HOST);
        }

        if (!isset($host)) {
            $urlApp = Str::of(Config::get('app.urls'))
                ->replaceMatches('#/*$#', '');

            $host = parse_url($urlApp, PHP_URL_HOST);
        }

        return $host ?? '';
    }

    /**
     * Process the indexing of URLs.
     *
     * @return string|array
     */
    private function process()
    {
        if (Config::get('laravel-indexnow.enable_request') !== true) {
            return 'Sending requests to IndexNow is currently disabled. To enable request sending, set the "enable_request" property to true in your configuration. If you do not have the package configuration file, install it using the following command: php artisan vendor:publish --tag=laravel-indexnow';
        }

        if (PreventSpan::isAllowed() == false) {
            return 'The use of IndexNow has been temporarily blocked to prevent potential spam.';
        }

        $this->parseUrls();

        $endpoint = Str::of("https://<searchengine>/indexnow")
            ->replace("<searchengine>", $this->getSearchEngine());

        $response = null;

        if (count($this->getUrls()) > 1) {
            $data            = [];
            $data['host']    = $this->getHost();
            $data['key']     = $this->getKey();
            $data['urlList'] = $this->getUrls();
            $response        = Http::post($endpoint, $data);
        } else if (count($this->getUrls()) == 1) {
            $endpoint = $endpoint->replace("<searchengine>", $this->getSearchEngine())
                ->replace("<url-changed>", $this->getUrls()[0])
                ->replace("<your-key>", $this->getKey());
            $response = Http::get($endpoint);
        } else {
            return 'No URLs provided for indexing.';
        }

        PreventSpan::detectPotentialSpam($response);

        return [
            'searchengine' => $this->getSearchEngine(),
            'status'       => $response->status(),
            'info'         => $response->reason(),
            'urls'         => (count($this->getUrls()) == 1) ? rawurldecode($this->getUrls()[0]) : $this->getUrls(),
        ];
    }
}
