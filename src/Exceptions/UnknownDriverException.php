<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;

class UnknownDriverException extends Exception
{
    /**
     * @var string
     */
    protected $message = "Unknown IndexNow driver.";

    /**
     * @var integer
     */
    protected $code = 404;
}
