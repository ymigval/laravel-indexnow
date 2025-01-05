<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;
use Ymigval\LaravelIndexnow\LogManager;

abstract class BaseLoggingException extends Exception
{
    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct($message, $code);

        $this->logException();
    }

    /**
     * Log the exception's message.
     */
    public function logException(): void
    {
        if (!empty($this->message)) {
            LogManager::addMessage($this->message);
        }
    }
}