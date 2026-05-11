<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\Gasto\GastoExcepcionDetailResource;
use App\Http\Resources\Gasto\GastoExcepcionResource;
use App\Models\GastoExcepcion;
use App\Services\ExcepcionService;
use App\Services\Gasto\ValidadorGastosService;
use Illuminate\Http\Request;

class GastoExcepcionController extends Controller
{
    public function resolver(Request $request, GastoExcepcion $excepcion, ExcepcionService $service)
    {
        $request->validate([
            'accion' => 'required|in:aprobado,rechazado',
            'comentario' => 'nullable|string'
        ]);

        $resultado = $service->resolver(
            $excepcion,
            $request->user(),
            $request->accion,
            $request->comentario
        );

        return ApiResponse::success([], $resultado['mensaje']);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = GastoExcepcion::with([
            'gasto.concepto',
            'gasto.solicitud.empleado'
        ])->where('estatus', 'pendiente');

        if ($user->hasRole('gerente')) {
            $query->where('nivel', 1);
        }

        if ($user->hasRole('admin')) {
            $query->where('nivel', 2);
        }

        $excepciones = $query
            ->latest()
            ->paginate(15);

        return ApiResponse::success(
            GastoExcepcionResource::collection($excepciones),
            'Bandeja de excepciones'
        );
    }

    public function show(GastoExcepcion $excepcion)
    {
        $excepcion->load([
            'gasto.concepto',
            'gasto.solicitud.empleado.user'
        ]);

        $politica = app(ValidadorGastosService::class)
            ->obtenerPolitica(
                $excepcion->gasto->empleado,
                $excepcion->gasto->concepto,
                $excepcion->gasto->fecha_gasto
            );

        return ApiResponse::success([
            'excepcion' => new GastoExcepcionDetailResource($excepcion),
            'politica' => $politica
        ], 'Detalle de excepción');
    }
}
