<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;
use Ymigval\LaravelIndexnow\LogManager;

class KeyFileDoesNotExistException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'The IndexNow key file does not exist. To create one, use the command: php artisan indexnow:generate-key';

    /**
     * @var int
     */
    protected $code = 404;

    public function __destruct()
    {
        LogManager::addLog($this->message);
    }
}
