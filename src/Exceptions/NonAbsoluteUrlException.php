<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;
use Ymigval\LaravelIndexnow\LogManager;

class NonAbsoluteUrlException extends Exception
{
    /**
     * @var string
     */
    protected $message = "Relative URLs detected. URLs submitted to IndexNow must be absolute or complete URLs.";

    /**
     * @var int
     */
    protected $code = 404;

    public function __destruct()
    {
        LogManager::addLog($this->message);
    }
}
