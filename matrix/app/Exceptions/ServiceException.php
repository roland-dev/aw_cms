<?php

namespace Matrix\Exceptions;

class ServiceException extends MatrixException
{
    //
    public function __construct(string $message = "", int $code = SYS_STATUS_SERVICE_ERROR, Throwable $previous = NULL)
    {
        $message = "Service Exception: $message .";
        parent::__construct($message, $code, $previous);
    }
}
