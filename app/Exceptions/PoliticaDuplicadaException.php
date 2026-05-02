<?php

namespace App\Exceptions;

use Exception;

class PoliticaDuplicadaException extends Exception
{
    public function __construct(
        string $message = 'Ya existe una política vigente para este rol, concepto y tipo de límite',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
