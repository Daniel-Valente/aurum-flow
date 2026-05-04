<?php

namespace App\Exceptions\Solicitudes;


class AutoAprobacionException extends \RuntimeException
{
    public function __construct(
        string $message = 'No puedes aprobar tu propia solicitud.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
