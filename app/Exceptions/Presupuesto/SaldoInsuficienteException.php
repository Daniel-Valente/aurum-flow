<?php

namespace App\Exceptions\Presupuesto;

use Exception;

class SaldoInsuficienteException extends Exception
{
    protected $message = 'Presupuesto insuficiente para esta operación.';
    protected $code = 422;

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
        if (request()->wantsJson()) {
            return response()->json([
                'error' => $this->getMessage(),
                'code' => $this->getCode(),
            ], $this->getCode());
        }

        return back()->with('error', $this->getMessage());
    }
}
