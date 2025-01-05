<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

class SearchEngineUnknownException extends BaseLoggingException
{
    public function __construct()
    {
        $message = 'Unknown search engine driver for IndexNow.';
        parent::__construct($message);
    }
}
