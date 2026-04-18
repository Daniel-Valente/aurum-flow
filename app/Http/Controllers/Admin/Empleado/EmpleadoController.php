<?php

namespace App\Http\Controllers\Admin\Empleado;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Empleado\StoreEmpleadoRequest;
use App\Http\Requests\Empleado\UpdateEmpleadoRequest;
use App\Http\Resources\Empleado\EmpleadoResource;
use App\Models\Empleado;
use App\Services\Empleado\EmpleadoService;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $query = Empleado::with(['user', 'area', 'centroCosto']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nombre_completo', 'like', "%{$search}%")
                    ->orWhere('numero_nomina', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('centro_costo_id')) {
            $query->where('centro_costo_id', $request->centro_costo_id);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $query->orderBy($sortBy, $sortDir);

        $perPage = min($request->input('per_page', 15), 100);

        $empleados = $query->paginate($perPage);

        return ApiResponse::success([
            'items' => EmpleadoResource::collection($empleados->items()),
            'pagination' => [
                'total' => $empleados->total(),
                'per_page' => $empleados->perPage(),
                'current_page' => $empleados->currentPage(),
                'last_page' => $empleados->lastPage(),
            ]
        ], 'Lista de empleados');
    }

    public function list()
    {
        $empleados = Empleado::where('estatus', true)
            ->with('centroCosto:id,nombre')
            ->orderBy('nombre_completo')
            ->get(['user_id', 'nombre_completo', 'centro_costo_id']);

        return ApiResponse::success($empleados, 'Lista simple de empleados');
    }

    public function store(StoreEmpleadoRequest $request, EmpleadoService $service)
    {
        $data = $service->create($request->validated());

        return ApiResponse::success($data, 'Empleado creado correctamente', 201);
    }

    public function update(UpdateEmpleadoRequest $request, Empleado $empleado, EmpleadoService $service)
    {
        $data = $service->update($empleado, $request->validated());

        return ApiResponse::success($data, 'Empleado actualizado');
    }

    public function destroy(Empleado $empleado, EmpleadoService $service)
    {
        $service->delete($empleado);

        return ApiResponse::success(null, 'Empleado eliminado');
    }

    public function toggleStatus(Empleado $empleado)
    {
        $empleado->update([
            'estatus' => !$empleado->estatus
        ]);

        return ApiResponse::success(
            ['estatus' => $empleado->estatus],
            'Estado actualizado'
        );
    }
}
