<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;

class KeyFileDoesNotExistException extends Exception
{
    /**
     * @var string
     */
    protected $message = "The IndexNow key file doesn't exist. To create one, use the command: artisan indexnow:newkey";

    /**
     * @var integer
     */
    protected $code = 404;
}
