<?php

namespace Matrix\Exceptions;

class UserException extends MatrixException
{
    //
    public function __construct(string $message = "", int $code = 0, Throwable $previous = NULL)
    {
        $message = "User Exception: $message .";
        parent::__construct($message, $code, $previous);
    }
}
