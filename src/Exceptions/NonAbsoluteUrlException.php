<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

class NonAbsoluteUrlException extends BaseLoggingException
{

    public function __construct()
    {
        $message = 'Relative URLs detected. URLs submitted to IndexNow must be absolute or complete URLs.';
        parent::__construct($message);
    }
}
