<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;
use Ymigval\LaravelIndexnow\LogManager;

class ExcessUrlsException extends Exception
{
    /**
     * @var string
     */
    protected $message = "You have exceeded the maximum allowed number of URLs to send to IndexNow.";

    /**
     * @var int
     */
    protected $code = 404;

    public function __destruct()
    {
        LogManager::addLog($this->message);
    }
}
