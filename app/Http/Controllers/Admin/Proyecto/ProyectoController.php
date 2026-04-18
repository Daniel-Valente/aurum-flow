<?php

namespace App\Http\Controllers\Admin\Proyecto;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Proyecto\StoreProyectoRequest;
use App\Http\Requests\Proyecto\UpdateProyectoRequest;
use App\Http\Resources\Proyecto\ProyectoResource;
use App\Models\Proyecto;
use App\Services\Proyecto\ProyectoService;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    public function index(Request $request)
    {
        $proyectos = Proyecto::with(['centroCosto', 'responsable'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('nombre', 'like', "%{$request->search}%")
                        ->orWhere('codigo', 'like', "%{$request->search}%")
                        ->orWhere('cliente', 'like', "%{$request->search}%");
                });
            })
            ->when($request->centro_costo_id, fn($q) => $q->where('centro_costo_id', $request->centro_costo_id))
            ->when($request->responsable_id, fn($q) => $q->where('responsable_id', $request->responsable_id))
            ->when(!is_null($request->estatus), fn($q) => $q->where('estatus', $request->estatus))
            ->latest()
            ->paginate(15);

        return ApiResponse::success(
            ProyectoResource::collection($proyectos)->response()->getData(true),
            'Lista de proyectos'
        );
    }

    public function list()
    {
        $proyectos = Proyecto::where('estatus', true)
            ->with([
                'centroCosto:id,nombre',
                'responsable:id,nombre_completo'
            ])
            ->orderBy('nombre')
            ->get([
                'id',
                'codigo',
                'nombre',
                'centro_costo_id',
                'responsable_id'
            ]);

        return ApiResponse::success($proyectos, 'Lista simple de proyectos');
    }

    public function store(StoreProyectoRequest $request, ProyectoService $service)
    {
        $proyecto = $service->create($request->validated());

        return ApiResponse::success(
            new ProyectoResource($proyecto),
            'Proyecto creado',
            201
        );
    }

    public function update(UpdateProyectoRequest $request, Proyecto $proyecto, ProyectoService $service)
    {
        $proyecto = $service->update($proyecto, $request->validated());

        return ApiResponse::success(
            new ProyectoResource($proyecto),
            'Proyecto actualizado'
        );
    }

    public function toggle(Proyecto $proyecto)
    {
        $proyecto->update([
            'estatus' => !$proyecto->estatus
        ]);

        return ApiResponse::success([
            'estatus' => $proyecto->estatus
        ], 'Estado actualizado');
    }

    public function destroy(Proyecto $proyecto, ProyectoService $service)
    {
        $service->delete($proyecto);

        return ApiResponse::success([], 'Proyecto eliminado');
    }
}
