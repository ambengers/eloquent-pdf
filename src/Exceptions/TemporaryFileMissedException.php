<?php

namespace Ambengers\EloquentPdf\Exceptions;

use Exception;

class TemporaryFileMissedException extends Exception
{
    /**
     * Set the exception message.
     *
     * @param  string $message
     * @return static
     */
    public static function withMessage($message = '')
    {
        return new static($message);
    }
}
