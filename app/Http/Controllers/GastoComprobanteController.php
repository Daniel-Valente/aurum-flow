<?php

namespace App\Http\Controllers;

use App\Models\GastoComprobante;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GastoComprobanteController extends Controller
{
public function download(GastoComprobante $comprobante)
    {
        $user = request()->user();

        if (
            $comprobante->solicitud->empleado->user_id !== $user->id &&
            !$user->can('gastos.ver.todos')
        ) {
            throw new AuthorizationException('No autorizado');
        }

        return response()->download(
            Storage::disk('private')->path($comprobante->archivo)
        );
    }
}
