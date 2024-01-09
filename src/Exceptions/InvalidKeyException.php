<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;
use Ymigval\LaravelIndexnow\LogManager;

class InvalidKeyException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Your IndexNow key is invalid. To create a new one, use the command: php artisan indexnow:generate-key';

    /**
     * @var int
     */
    protected $code = 404;

    public function __destruct()
    {
        LogManager::addLog($this->message);
    }
}
