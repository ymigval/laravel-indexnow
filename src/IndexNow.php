<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Exceptions\ExcessUrlsException;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\Exceptions\UnknownDriverException;
use Illuminate\Http\Client\Response;

class IndexNow
{
    /**
     * List of available drivers
     */
    const DRIVERS = [
        'indexnow' => "api.indexnow.org",
        'microsoft_bing' => "www.bing.com",
        'naver' => "searchadvisor.naver.com",
        'seznam' => "search.seznam.cz",
        'yandex' => "yandex.com"
    ];


    /**
     * @var string
     */
    private $driver;


    /**
     * @var array
     */
    private $urls = [];


    /**
     * Initialization
     * @param string $driver
     */
    public function __construct(string $driver)
    {
        $this->setDriver($driver);   
    }


    /**
     * Set driver
     * 
     * @param string $driver
     * @return void
     * @throws UnknownDriverException
     */
    public function setDriver(string $driver): void {
        if(array_key_exists($driver, self::DRIVERS)) {
            $this->driver = self::DRIVERS[$driver];
        } else {
            throw new UnknownDriverException();
        }       
    }


    /**
     * Get established driver
     * 
     * @return string
     */
    public function getDriver(): string {
        return $this->driver;    
    }


    /**
     * Set Urls
     * @param  string|array $url 
     * @return $this
     */
    public function setUrl($url): IndexNow {
        if(is_array($url)) {
            $this->urls = array_merge($this->urls, $url);
        } else {
            $this->urls[] = $url;
        }

        return $this;
    }

    /**
     * Get Urls
     * 
     * @return array
     */
    public function getUrl(): array {
        return array_values(array_unique($this->urls));    
    }


    /**
     * Get key IndexNow
     *
     * @return string
     * @throws KeyFileDoesNotExistException | InvalidKeyException
     */
    public function getKey(): string
    {
        return KeyIndexNow::getKey();      
    }

    /**
     * Submit one or more Urls
     * 
     * @param  string|array|null $url 
     * @return [type]       [description]
     */
    public function submit($url = null) {

        if(!is_null($url)) {
            $this->setUrl($url);
        }

        $this->process();
    }


    /**
     * @return void
     * @throws ExcessUrlsException
     */
    private function parseUrls(): void {
        $urlApp = Str::of(Config::get('app.urls'))
            ->replaceMatches('#/*$#', '');

        if (count($this->urls) > 10000) {
            throw new ExcessUrlsException();
        }

        $this->urls = array_map(function($url) use ($urlApp) {

            // If the host is not defined add in of the application
            if(is_null(parse_url($url, PHP_URL_HOST))) {
                $url = Str::of($urlApp)->append($url);
            }

            return $url;
        }, $this->urls);


        // If there is only one URL, a GET request will be sent.
        // Encode URL following RFC-3986 standard.
        if(count($this->urls) == 1) {
            $this->urls[0] = rawurlencode($this->urls[0]);
        }
    }


    /**
     * @return string
     */
    private function host(): string {
        
        // Get host from urls
        if(isset($this->getUrl()[0])) {
            $host = parse_url(rawurldecode($this->getUrl()[0]), PHP_URL_HOST);
        }


        // Get the host from the url of the application
        if(!isset($host)) {
            $urlApp = Str::of(Config::get('app.urls'))
                ->replaceMatches('#/*$#', '');

            $host = parse_url($urlApp, PHP_URL_HOST);
        }

        return $host ?? '';
    }


    private function process() {

        $this->parseUrls();

        $endpoint = Str::of("https://<searchengine>/indexnow")
            ->replace("<searchengine>", $this->getDriver());

        $response = null;

        if(count($this->getUrl()) > 1) {
            $data = [];
            $data['host'] = $this->host();
            $data['key'] = $this->getKey();
            $data['urlList'] = $this->getUrl();
            $response = Http::post($endpoint, $data);
        } else if(count($this->getUrl()) == 1) {
            $endpoint = $endpoint->replace("<searchengine>", $this->getDriver())
                ->replace("<url-changed>", $this->getUrl()[0])
                ->replace("<your-key>", $this->getKey());
            $response = Http::get($endpoint);
        } else {

        }


        //$status = $response->status()
        //$response->reason()


        dd($response);

    
    }
}
