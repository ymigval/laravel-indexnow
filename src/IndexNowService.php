<?php

namespace Ymigval\LaravelIndexnow;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Exceptions\ExcessUrlsException;
use Ymigval\LaravelIndexnow\Exceptions\MixedException;
use Ymigval\LaravelIndexnow\Exceptions\NonAbsoluteUrlException;
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
     * Get the IndexNow API key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return IndexNowApiKeyManager::getApiKey();
    }

    /**
     * Set URL for indexing.
     *
     * @param  string $url
     * @return $this
     */
    public function setUrl(string $url): IndexNowService
    {
        $this->urls[] = $url;

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
     * Set URLs for indexing.
     *
     * @param  string|array $url
     * @return void
     */
    private function setUrls($url): void
    {
        if (is_array($url)) {
            $this->urls = array_merge($this->urls, $url);
        } else {
            $this->urls[] = $url;
        }
    }

    /**
     * Parse and prepare URLs for indexing.
     *
     * @return void
     * @throws ExcessUrlsException | NonAbsoluteUrlException
     */
    private function parseUrls(): void
    {
        $urlApp = Str::of(Config::get('app.url'))
            ->replaceMatches('#/*$#', '');

        if (count($this->urls) > 10000) {
            throw new ExcessUrlsException();
        }

        foreach ($this->urls as $index => $url) {
            // Si la url no tiene un host se considera que es relactiva
            // Trata de convertirla en absoluta concatenandola con la url base (dominio) de la aplicacion
            if (is_null(parse_url($url, PHP_URL_HOST))) {
                $url = Str::of($urlApp)->append($url);
            }

            // Verificar si la url es abosula
            if (is_null(parse_url($url, PHP_URL_HOST))) {
                throw new NonAbsoluteUrlException();
            }

            $this->urls[$index] = $url;
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

        if (Config::get('indexnow.ignore_production_environment') !== true && App::isProduction() == false) {
            return 'Sending requests to IndexNow is currently disabled in the local environment. To enable request sending in any environment, set the property "ignore_production_environment" to true in the configuration file. If you do not have the package configuration file, you can install it using the following command: php artisan vendor:publish --tag="indexnow"';
        }

        if (PreventSpan::isAllowed() == false) {
            return 'The use of IndexNow has been temporarily blocked to prevent potential spam.';
        }

        $this->parseUrls();

        $endpoint = Str::of("https://<searchengine>/indexnow")
            ->replace("<searchengine>", $this->getSearchEngine());
        $response = null;

        try {
            if (count($this->getUrls()) > 1) {
                $data            = [];
                $data['host']    = $this->getHost();
                $data['key']     = $this->getKey();
                $data['urlList'] = $this->getUrls();

                $response = Http::post($endpoint, $data);
            } else if (count($this->getUrls()) == 1) {
                $endpoint = Str::of($endpoint)
                    ->append('?')
                    ->append(http_build_query(['url' => $this->getUrls()[0], 'key' => $this->getKey()]));

                $response = Http::get($endpoint);
            } else {
                return 'No URLs provided for indexing.';
            }
        } catch (Exception $e) {
            throw new MixedException($e->getMessage(), $e->getCode());
        }

        PreventSpan::detectPotentialSpam($response);

        return [
            'searchengine' => $this->getSearchEngine(),
            'status'       => $response->status(),
            'info'         => $response->reason(),
            'urls'         => $this->getUrls(),
        ];
    }
}
