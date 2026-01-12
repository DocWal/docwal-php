<?php

namespace DocWal\Exceptions;

use Exception;

/**
 * Base exception for all DocWal SDK errors
 */
class DocWalException extends Exception
{
    /**
     * DocWalException constructor
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param Exception|null $previous Previous exception
     */
    public function __construct(string $message = "", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
