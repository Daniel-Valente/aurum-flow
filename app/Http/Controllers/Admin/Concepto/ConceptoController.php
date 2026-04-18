<?php

namespace App\Http\Controllers\Admin\Concepto;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Concepto\StoreConceptoRequest;
use App\Http\Requests\Concepto\UpdateConceptoRequest;
use App\Http\Resources\Concepto\ConceptoResource;
use App\Models\Concepto;
use App\Services\Concepto\ConceptoService;
use Illuminate\Http\Request;

class ConceptoController extends Controller
{
    public function index(Request $request)
    {
        $conceptos = Concepto::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->search}%")
                    ->orWhere('codigo', 'like', "%{$request->search}%");
            })
            ->when($request->tipo_aplicacion, function ($q) use ($request) {
                $q->where('tipo_aplicacion', $request->tipo_aplicacion);
            })
            ->when(!is_null($request->estatus), function ($q) use ($request) {
                $q->where('estatus', $request->estatus);
            })
            ->when($request->vigentes, function ($q) {
                $q->where(function ($q) {
                    $q->whereNull('vigencia_desde')
                        ->orWhere('vigencia_desde', '<=', now());
                })->where(function ($q) {
                    $q->whereNull('vigencia_hasta')
                        ->orWhere('vigencia_hasta', '>=', now());
                });
            })
            ->orderBy('orden')
            ->paginate(15);

        return ApiResponse::success(
            ConceptoResource::collection($conceptos),
            'Lista de conceptos'
        );
    }

    public function list()
    {
        $conceptos = Concepto::where('estatus', true)
            ->orderBy('nombre')
            ->get();

        return ApiResponse::success($conceptos, 'Lista simple de conceptos');
    }

    public function store(StoreConceptoRequest $request, ConceptoService $service)
    {
        $concepto = $service->create($request->validated());

        return ApiResponse::success(
            new ConceptoResource($concepto),
            'Concepto creado',
            201
        );
    }

    public function update(UpdateConceptoRequest $request, Concepto $concepto, ConceptoService $service)
    {
        $concepto = $service->update($concepto, $request->validated());

        return ApiResponse::success(
            new ConceptoResource($concepto),
            'Concepto actualizado'
        );
    }

    public function destroy(Concepto $concepto, ConceptoService $service)
    {
        $service->delete($concepto);

        return ApiResponse::success(null, 'Concepto eliminado');
    }

    public function toggle(Concepto $concepto)
    {
        $concepto->update([
            'estatus' => !$concepto->estatus
        ]);

        return ApiResponse::success(
            ['estatus' => $concepto->estatus],
            'Estado actualizado'
        );
    }
}
