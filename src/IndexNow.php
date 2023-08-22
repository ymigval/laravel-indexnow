<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;

class IndexNow
{
    /**
     * List of hosts
     */
    const HOST_INDEXNOW = "api.indexnow.org";
    const HOST_MICROSOFT_BING = "www.bing.com";
    const HOST_NAVER = "searchadvisor.naver.com";
    const HOST_SEZNAM = "search.seznam.cz";
    const HOST_YANDEX = "yandex.com";


    const ENDPOINT = "/indexnow";


    /**
     * Mode
     * @var int|null
     */
    private $mode = null;


    /**
     * @var string
     */
    private $host = null;

    /**
     * Get key IndexNow
     *
     * @return string
     * @throws KeyFileDoesNotExistException | InvalidKeyException
     */
    public function getKey()
    {
        return KeyIndexNow::getKey();      
    }


    public function url() {
        $this->submittingOneURL();

        $this->process();
    }


    private function submittingOneURL() {
        $this->mode = 1;
    }

    private function submittingSetURLs() {
        $this->mode = 2;
    }

    private function getMode() {
        return $this->mode;
    }

    private function process() {

        if($this->getMode() === 1) {

            $query = Str::of("https://<searchengine>/<end-point>?url=<url-changed>&key=<your-key>")
                ->replace("<searchengine>", Self::HOST_MICROSOFT_BING)
                ->replace("<end-point>", self::ENDPOINT)
                ->replace("<url-changed>", 'my-url')
                ->replace("<your-key>", $this->getKey());



            dd($query);

            //$response = Http::get();
        } else if($this->getMode() === 2) {

        } else {
            // ..
        }
    }
}
