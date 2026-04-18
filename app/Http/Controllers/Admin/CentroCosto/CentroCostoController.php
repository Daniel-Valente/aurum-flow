<?php

namespace App\Http\Controllers\Admin\CentroCosto;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CentroCosto\StoreCentroCostoRequest;
use App\Http\Requests\CentroCosto\UpdateCentroCostoRequest;
use App\Http\Resources\CentroCosto\CentroCostoResource;
use App\Models\CentroCosto;
use App\Services\CentroCosto\CentroCostoService;
use Illuminate\Http\Request;

class CentroCostoController extends Controller
{
    public function index(Request $request)
    {
        $centros = CentroCosto::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('codigo', 'like', "%{$request->search}%")
                        ->orWhere('nombre', 'like', "%{$request->search}%");
                });
            })
            ->when(!is_null($request->estatus), function ($q) use ($request) {
                $q->where('estatus', $request->estatus);
            })
            ->latest()
            ->paginate(15);

        return ApiResponse::success(
            CentroCostoResource::collection($centros)->response()->getData(true),
            'Lista de centros de costo'
        );
    }

    public function list()
    {
        $centroCosto = CentroCosto::where('estatus', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        return ApiResponse::success($centroCosto, 'Lista simple de centro costo');
    }

    public function store(StoreCentroCostoRequest $request, CentroCostoService $service)
    {
        $centroCosto = $service->create($request->validated());

        return ApiResponse::success(
            new CentroCostoResource($centroCosto),
            'Centro Costo creado',
            201
        );
    }

    public function update(UpdateCentroCostoRequest $request, CentroCosto $centroCosto, CentroCostoService $service)
    {
        $centroCosto = $service->update($centroCosto, $request->validated());

        return ApiResponse::success(
            new CentroCostoResource($centroCosto),
            'Centro Costo actualizado'
        );
    }

    public function destroy(CentroCosto $centroCosto, CentroCostoService $service)
    {
        $service->delete($centroCosto);

        return ApiResponse::success(null, 'Centro Costo eliminado');
    }

    public function toggle(CentroCosto $centroCosto)
    {
        $centroCosto->update([
            'estatus' => !$centroCosto->estatus
        ]);

        return ApiResponse::success([
            'estatus' => $centroCosto->estatus
        ], 'Estado actualizado');
    }
}
