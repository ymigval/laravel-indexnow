<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;

class ExcessUrlsException extends Exception
{
    /**
     * @var string
     */
    protected $message = "You have many URLs to send to IndexNow.";

    /**
     * @var integer
     */
    protected $code = 404;
}
