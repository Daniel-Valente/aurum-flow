<?php

namespace App\Exceptions\Solicitudes;

use Exception;

class SolicitudBloqueadaException extends Exception
{
    public function __construct(
        string $message = 'La solicitud no puede enviarse porque contiene conceptos que exceden el límite permitido sin posibilidad de excepción.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
