<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;
use Ymigval\LaravelIndexnow\LogManager;

class MixedException extends Exception
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var int
     */
    protected $code;

    public function __construct(string $message, int $code = 0)
    {
        $this->message = $message;
        $this->code = $code;
    }

    public function __destruct()
    {
        LogManager::addLog($this->message);
    }
}
