<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Throwable;

class MixedException extends BaseLoggingException
{
    /**
     * Creates a new MixedException instance.
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Throwable|null $previous The previous throwable used for exception chaining
     */
    public function __construct(string     $message = "An error occurred with the IndexNow API request",
                                int        $code = 0,
                                ?Throwable $previous = null)
    {
        parent::__construct($message, $code);

        if ($previous) {
            $this->file = $previous->getFile();
            $this->line = $previous->getLine();
        }
    }
}
