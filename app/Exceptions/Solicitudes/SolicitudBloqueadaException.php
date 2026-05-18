<?php

namespace App\Exceptions\Solicitudes;

use Exception;

class SolicitudBloqueadaException extends Exception
{
    protected $message = 'La solicitud está bloqueada y no puede ser modificada.';
    protected $code = 403;

    public function __construct(string $message = null, int $code = null)
    {
        if ($message) {
            $this->message = $message;
        }

        if ($code) {
            $this->code = $code;
        }

        parent::__construct($this->message, $this->code);
    }

    public function render()
    {
        return response()->json([
            'error' => $this->getMessage(),
            'code' => $this->getCode(),
        ], $this->getCode());
    }
}
