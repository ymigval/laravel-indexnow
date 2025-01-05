<?php

namespace Ymigval\LaravelIndexnow;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Exceptions\ExcessUrlsException;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\MixedException;
use Ymigval\LaravelIndexnow\Exceptions\NonAbsoluteUrlException;
use Ymigval\LaravelIndexnow\Exceptions\SearchEngineUnknownException;

class IndexNowService
{
    /**
     * @var string | null
     */
    private string $searchengine;

    private string $key;

    private ?string $keyFile = null;

    private array $urls = [];

    /**
     * Initialize the IndexNow service.
     *
     * @param  string  $searchengine The Search Engine to be used for indexing.
     *
     * @throws SearchEngineUnknownException
     */
    public function __construct(string $searchengine)
    {
        $this->setSearchEngine($searchengine);
        $this->key = IndexNowApiKeyManager::getApiKey();
    }

    /**
     * Set the Search Engine for the IndexNow service.
     *
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
     */
    public function getSearchEngine(): string
    {
        return (string) $this->searchengine;
    }

    /**
     * Get the IndexNow API key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Hosting a text key file within your host
     *
     * @throws InvalidKeyException
     */
    public function keyFile(string $file): void
    {
        $apiKey = Http::get($file)->body();
        $apiKeyLength = strlen($apiKey);
        $countNewLines = substr_count($apiKey, "\n");

        if ($countNewLines != 0 || $apiKeyLength < 8 || $apiKeyLength > 128) {
            throw new InvalidKeyException();
        }

        $this->keyFile = $file;
        $this->key = $apiKey;
    }

    /**
     * Set URL for indexing.
     *
     * @return $this
     */
    public function setUrl(string $url): IndexNowService
    {
        $this->urls[] = $url;

        return $this;
    }

    /**
     * Get URLs to be indexed.
     */
    public function getUrls(): array
    {
        return array_values(array_unique($this->urls));
    }

    /**
     * Submit one or more URLs for indexing.
     *
     * @param  string|array|null  $url
     * @return string|array
     */
    public function submit($url = null)
    {
        if (! is_null($url)) {
            $this->setUrls($url);
        }

        $process = $this->process();

        LogManager::addLog($process);

        return $process;
    }

    /**
     * Set URLs for indexing.
     *
     * @param  string|array  $url
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
            // If the url does not have a host it is considered relative.
            // Try to make it absolute by concatenating it with the base url (domain) of the application.
            if (is_null(parse_url($url, PHP_URL_HOST))) {
                $url = Str::of($urlApp)->append($url);
            }

            // Check if the url is absolute
            if (is_null(parse_url($url, PHP_URL_HOST))) {
                throw new NonAbsoluteUrlException();
            }

            $this->urls[$index] = (string) $url;
        }
    }

    /**
     * Get the host for the URLs.
     */
    private function getHost(): string
    {
        if (isset($this->getUrls()[0])) {
            $host = parse_url(rawurldecode($this->getUrls()[0]), PHP_URL_HOST);
        }

        if (! isset($host)) {
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
     *
     * @throws MixedException
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

        $endpoint = Str::of('https://<searchengine>/indexnow')
            ->replace('<searchengine>', $this->getSearchEngine());
        $response = null;

        try {
            if (count($this->getUrls()) > 1) {
                $data = [];
                $data['host'] = $this->getHost();
                $data['key'] = $this->getKey();

                if ($this->keyFile) {
                    $data['keyLocation'] = $this->keyFile;
                }

                $data['urlList'] = $this->getUrls();

                $response = Http::post($endpoint, $data);
            } elseif (count($this->getUrls()) == 1) {
                $data = [];
                $data['url'] = $this->getUrls()[0];
                $data['key'] = $this->getKey();

                if ($this->keyFile) {
                    $data['keyLocation'] = $this->keyFile;
                }

                $endpoint = Str::of($endpoint)
                    ->append('?')
                    ->append(http_build_query($data));

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
            'status' => $response->status(),
            'info' => $response->reason(),
            'urls' => $this->getUrls(),
        ];
    }
}
