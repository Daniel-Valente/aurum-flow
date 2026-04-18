<?php

namespace App\Http\Controllers\Admin\PoliticaGasto;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PoliticaGasto\StorePoliticaGastoRequest;
use App\Models\PoliticaGasto;
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

        return ApiResponse::success($politicas, 'Lista de políticas');
    }

    public function store(StorePoliticaGastoRequest $request, PoliticaGastoService $service)
    {
        $politica = $service->create($request->validated());

        return ApiResponse::success($politica, 'Política creada');
    }

    public function destroy(PoliticaGasto $politica, PoliticaGastoService $service)
    {
        $service->delete($politica);

        return ApiResponse::success([], 'Política eliminada');
    }
}
