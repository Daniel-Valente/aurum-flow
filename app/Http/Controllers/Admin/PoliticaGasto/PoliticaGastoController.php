<?php

namespace App\Http\Controllers\Admin\PoliticaGasto;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PoliticaGasto\StorePoliticaGastoRequest;
use App\Models\PoliticaGasto;
use App\Models\PoliticaGastoAuditoria;
use App\Models\PoliticaGastoVersion;
use App\Services\PoliticaGasto\PoliticaGastoService;
use Illuminate\Http\Request;

class PoliticaGastoController extends Controller
{
    public function index(Request $request)
    {
        $politicas = PoliticaGasto::with(['role', 'concepto'])
            ->when($request->role_id, fn($q) => $q->where('role_id', $request->role_id))
            ->when($request->concepto_id, fn($q) => $q->where('concepto_id', $request->concepto_id))
            ->latest()
            ->paginate(15);

        return ApiResponse::success($politicas, 'Lista de políticas (actuales)');
    }

    public function store(StorePoliticaGastoRequest $request, PoliticaGastoService $service)
    {
        $politica = $service->create(
            $request->validated(),
            auth()->user()
        );

        return ApiResponse::success($politica, 'Política creada');
    }

    public function update(
        StorePoliticaGastoRequest $request,
        PoliticaGasto $politica,
        PoliticaGastoService $service
    ) {
        $politica = $service->update(
            $politica,
            $request->validated(),
            auth()->user()
        );

        return ApiResponse::success($politica, 'Política actualizada');
    }

    public function destroy(PoliticaGasto $politica, PoliticaGastoService $service)
    {
        $service->delete($politica, auth()->user());

        return ApiResponse::success([], 'Política eliminada');
    }

    public function versiones(PoliticaGasto $politica)
    {
        $versiones = PoliticaGastoVersion::where('politica_id', $politica->id)
            ->with(['role', 'concepto'])
            ->latest()
            ->get();

        return ApiResponse::success($versiones, 'Historial de versiones');
    }

    public function auditoria(PoliticaGasto $politica)
    {
        $logs = PoliticaGastoAuditoria::where('politica_id', $politica->id)
            ->latest()
            ->get();

        return ApiResponse::success($logs, 'Auditoría de la política');
    }
}
