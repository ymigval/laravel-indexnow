<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;
use Ymigval\LaravelIndexnow\LogManager;

class SearchEngineUnknownException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Unknown search engine driver for IndexNow.';

    /**
     * @var int
     */
    protected $code = 404;

    public function __destruct()
    {
        LogManager::addLog($this->message);
    }
}
