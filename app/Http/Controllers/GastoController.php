<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Gasto\StoreGastoRequest;
use App\Http\Resources\Gasto\GastoTimelineResource;
use App\Models\Gasto;
use App\Models\GastoAuditoria;
use App\Models\GastoComprobante;
use App\Services\Gasto\GastoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GastoController extends Controller
{
    public function store(StoreGastoRequest $request, GastoService $service)
    {
        $resultado = $service->crear($request->validated());

        if ($resultado['error']) {
            return ApiResponse::error($resultado['mensaje'], 422);
        }

        return ApiResponse::success(
            $resultado['gasto'],
            $resultado['mensaje']
        );
    }

    public function timeline($gastoId)
    {
        $timeline = GastoAuditoria::where('gasto_id', $gastoId)
            ->with('actor:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return ApiResponse::success(
            GastoTimelineResource::collection($timeline),
            'Timeline del gasto'
        );
    }

    public function subirComprobante(
        Gasto $gasto,
        Request $request,
        GastoService $service
    ) {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,png|max:5120',
            'monto' => 'nullable|numeric',
            'uuid' => 'nullable|string'
        ]);

        return ApiResponse::success(
            $service->subirComprobante(
                $gasto,
                $request->user(),
                $request->file('archivo'),
                $request->all()
            ),
            'Comprobante subido'
        );
    }

    public function validarManual(GastoComprobante $comprobante, Request $request)
    {
        $user = auth()->user;

        if (!$user->can('gastos.validar')) {
            throw new AuthorizationException();
        }

        $comprobante->update([
            'validacion_manual' => $request->accion, // aprobado | rechazado
            'validado_por' => $user->id
        ]);

        return ApiResponse::success($comprobante, 'Validación actualizada');
    }
}
