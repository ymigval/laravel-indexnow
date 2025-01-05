<?php

namespace Ymigval\LaravelIndexnow;

use Exception;
use Illuminate\Support\Facades\App;
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
     */
    public function __construct(string $searchengine)
    {
        $this->setSearchEngine($searchengine);
        $this->key = IndexNowApiKeyManager::fetchOrGenerate();
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
     * Retrieves the key associated with the current instance.
     *
     * @return string The key stored within the instance.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Sets the API key file path and validates the API key retrieved from the file.
     *
     * @param string $file The file path containing the API key.
     * @return void
     * @throws InvalidKeyException If the API key is invalid, has incorrect length, or contains line breaks.
     */
    public function keyFile(string $file): void
    {
        $apiKey = Http::get($file)->body();
        if (substr_count($apiKey, "\n") !== 0 || strlen($apiKey) < 8 || strlen($apiKey) > 128) {
            throw new InvalidKeyException();
        }

        $this->keyFile = $file;
        $this->key = $apiKey;
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
        $urlApp = Str::of(Config::get('app.url'))
            ->replaceMatches('#/*$#', ''); // Ensure base URL is clean

        if (count($this->urls) > 10000) {
            throw new ExcessUrlsException();
        }

        foreach ($this->urls as $index => $url) {
            // If the URL does not have a host, it is considered relative.
            // Try to make it absolute by concatenating it with the base URL.
            if (is_null(parse_url($url, PHP_URL_HOST))) {
                $url = Str::of($urlApp)->append($url);
            } else {
                $url = Str::of($url); // Ensure `$url` is always Stringable
            }

            // Check if the URL is valid and absolute
            if (is_null(parse_url($url, PHP_URL_HOST))) {
                throw new NonAbsoluteUrlException();
            }

            $this->urls[$index] = (string) $url; // Convert back to string for final assignment
        }
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
     * Processes URL submission to the IndexNow API based on the provided configuration and environment settings.
     *
     * This method validates the environment, checks spam prevention mechanisms, prepares the URLs,
     * and sends them to the appropriate search engine endpoint for indexing. Responses are handled
     * accordingly, and potential exceptions are captured and rethrown as MixedException.
     *
     * @return array|string Returns an array containing the search engine name, response status, info,
     * and URLs if the submission is successful, or a string message for specific cases like a disabled
     * environment, a temporary block due to spam prevention, or when no URLs are provided for indexing.
     * @throws MixedException If an error occurs during the HTTP request to the IndexNow API.
     */
    private function process(): array|string
    {
        if (! Config::get('indexnow.enable_submissions')) {
            return 'Enable Submissions is set to false. To allow submissions, please check the configuration file and set it to true.';
        }

        if (!PreventSpan::isAllowed()) {
            return 'The use of IndexNow has been temporarily blocked to prevent potential spam.';
        }

        $this->parseUrls();
        $urls = $this->getUrls();

        $endpoint = Str::of('https://<searchengine>/indexnow')
            ->replace('<searchengine>', $this->getSearchEngine());

        try {
            if (count($urls) > 1) {
                $response = Http::post((string)$endpoint, $this->buildRequestData());
            } elseif (count($urls) === 1) {
                $response = Http::get((string)$endpoint->append('?')->append(http_build_query([
                    'url' => $urls[0],
                    'key' => $this->key,
                    'keyLocation' => $this->keyFile,
                ])));
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
            'urls' => $urls,
        ];
    }
}
