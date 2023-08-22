<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;

class InvalidKeyException extends Exception
{
    /**
     * @var string
     */
    protected $message = "Your IndexNow key is not valid. To create a new one, use the command: artisan indexnow:newkey";

    /**
     * @var integer
     */
    protected $code = 404;
}
