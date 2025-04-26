<?php

namespace Ymigval\LaravelIndexnow;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Exceptions\ExcessUrlsException;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\MixedException;
use Ymigval\LaravelIndexnow\Exceptions\NonAbsoluteUrlException;
use Ymigval\LaravelIndexnow\Exceptions\SearchEngineUnknownException;

class IndexNowService
{
    private ?string $searchengine = null;
    private string $key;
    private ?string $keyFile = null;
    private array $urls = [];

    /**
     * Initialize the IndexNow service with the given search engine.
     *
     * @throws SearchEngineUnknownException
     * @throws InvalidKeyException
     */
    public function __construct(string $searchengine)
    {
        $this->setSearchEngine($searchengine);
        $this->key = IndexNowApiKeyManager::getKey();
    }

    /**
     * Sets the search engine driver URL based on the provided search engine name.
     *
     * @param string $searchengine The name of the search engine to be set.
     * @return void
     * @throws SearchEngineUnknownException If the provided search engine name is not recognized or invalid.
     */
    public function setSearchEngine(string $searchengine): void
    {
        $this->searchengine = SearchEngine::getDriverUrl($searchengine);

        if (is_null($this->searchengine)) {
            throw new SearchEngineUnknownException();
        }
    }

    /**
     * Retrieves the currently set search engine driver URL.
     *
     * @return string The driver URL of the current search engine or an empty string if no search engine is set.
     */
    public function getSearchEngine(): string
    {
        return $this->searchengine ?? '';
    }

    /**
     * Adds the provided URL to the list of URLs and returns the current instance.
     *
     * @param string $url The URL to be added.
     * @return static Returns the current instance for method chaining.
     */
    public function setUrl(string $url): static
    {
        $this->urls[] = $url;
        return $this;
    }

    /**
     * Retrieves a unique list of URLs stored in the instance.
     *
     * @return array An array containing the unique URLs.
     */
    public function getUrls(): array
    {
        return array_values(array_unique($this->urls));
    }

    /**
     * Processes the submission for the provided URLs and returns the result.
     *
     * @param array|string|null $url The URL(s) to be processed. Can be a string, an array of strings, or null.
     * @return array|string The result of the processing, either as an array or a string.
     * @throws MixedException
     */
    public function submit(array|string $url = null): array|string
    {
        if ($url !== null) {
            $this->setUrls($url);
        }

        $process = $this->process();
        LogManager::addMessage($process);

        return $process;
    }

    private function setUrls(array|string $url): void
    {
        $this->urls = is_array($url)
            ? array_merge($this->urls, $url)
            : array_merge($this->urls, [$url]);
    }

    /**
     * Parses and validates an array of URLs. Ensures all URLs are absolute by appending the base application URL to relative ones.
     * Processes each URL to confirm validity and stores the result. Throws exceptions for invalid or excessive URLs.
     *
     * @return void
     * @throws ExcessUrlsException If the number of URLs exceeds the allowed limit.
     * @throws NonAbsoluteUrlException If a URL is determined to be invalid or non-absolute after processing.
     */
    private function parseUrls(): void
    {
        if (empty($this->urls)) {
            return;
        }

        if (count($this->urls) > 10000) {
            throw new ExcessUrlsException();
        }

        $applicationBaseUrl = Str::of(Config::get('app.url'))
            ->trim()
            ->replaceMatches('#/*$#', '');

        foreach ($this->urls as $index => $rawUrl) {
            $normalizedUrl = $this->normalizeAndValidateUrl($rawUrl, $applicationBaseUrl);
            $this->urls[$index] = (string)$normalizedUrl;
        }
    }

    private function normalizeAndValidateUrl(string $url, string $baseUrl): string
    {
        $url = Str::of($url)->trim();

        $urlComponents = parse_url($url);

        if (empty($urlComponents['scheme']) || empty($urlComponents['host'])) {
            $url = Str::of($baseUrl)->append($url);
            $urlComponents = parse_url($url);
        }

        if (empty($urlComponents['scheme']) || empty($urlComponents['host'])) {
            throw new NonAbsoluteUrlException();
        }

        return $url;
    }

    /**
     * Constructs and returns the request data as an associative array.
     *
     * @return array The constructed request data containing key, host, urlList, and optionally keyLocation.
     */
    private function buildRequestData(): array
    {
        $data = [
            'key' => $this->key,
            'host' => $this->getHost(),
            'urlList' => $this->getUrls(),
        ];

        if ($this->keyFile) {
            $data['keyLocation'] = $this->keyFile;
        }

        return $data;
    }

    /**
     * Retrieves the host from the list of URLs or falls back to the application's base URL if no valid host is found.
     *
     * @return string The extracted host from the URLs or the application's base URL host. Returns an empty string if the host cannot be determined.
     */
    private function getHost(): string
    {
        $urls = $this->getUrls();
        $host = isset($urls[0]) ? parse_url(rawurldecode($urls[0]), PHP_URL_HOST) : null;

        if (!$host) {
            $appBaseUrl = Str::of(Config::get('app.url'))->replaceMatches('#/*$#', '');
            $host = parse_url((string)$appBaseUrl, PHP_URL_HOST);
        }

        return $host ?? '';
    }


    /**
     * Processes the IndexNow submission by validating configurations, parsing URLs, and sending a request to the search engine's API.
     * Returns either an informational message or an array with detailed submission information.
     *
     * @return array|string Returns an array containing submission details (search engine, status, info, and URLs)
     *                      or a string message describing the result or issue encountered.
     * @throws NonAbsoluteUrlException If one or more provided URLs are not absolute.
     * @throws ExcessUrlsException If the quantity of URLs exceeds the allowed limit for submission.
     * @throws MixedException If a general error occurs during the process.
     */
    private function process(): array|string
    {
        if (! Config::get('indexnow.enable_submissions')) {
            return 'Enable Submissions is set to false. To allow submissions, please check the configuration file and set it to true.';
        }

        if (!PreventSpam::isAllowed()) {
            return 'The use of IndexNow has been temporarily blocked to prevent potential spam.';
        }

        try {
            $this->parseUrls();
            $urls = $this->getUrls();

            $endpoint = Str::of('https://<searchengine>/indexnow')
                ->replace('<searchengine>', $this->getSearchEngine());

            if (count($urls) > 1) {
                $response = Http::post((string)$endpoint, $this->buildRequestData());
            } elseif (count($urls) === 1) {
                $queryParams = [
                    'url' => $urls[0],
                    'key' => $this->key,
                ];

                if ($this->keyFile) {
                    $queryParams['keyLocation'] = $this->keyFile;
                }

                $response = Http::get((string)$endpoint->append('?')->append(http_build_query($queryParams)));
            } else {
                return 'No URLs provided for indexing.';
            }
        } catch (NonAbsoluteUrlException|ExcessUrlsException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new MixedException($e->getMessage(), $e->getCode());
        }

        // Get URLs for logging
        $urlToLog = count($urls) === 1 ? $urls[0] : (count($urls) > 1 ? $urls[0] : null);

        // Pass the URL information to improve logging
        PreventSpam::detectPotentialSpam($response, $urlToLog);

        return [
            'searchengine' => $this->getSearchEngine(),
            'status' => $response->status(),
            'info' => $response->reason(),
            'urls' => $urls,
        ];
    }
}
