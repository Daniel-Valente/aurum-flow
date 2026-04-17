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
        $empleado = Empleado::with(['user', 'area', 'centroCosto'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('nombre_completo', 'like', "%{$request->search}%")
                    ->orWhere('numero_nomina', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate(15);

        return ApiResponse::success(
            EmpleadoResource::collection($empleado),
            'Lista de empleado'
        );
    }

    public function store(StoreEmpleadoRequest $request, EmpleadoService $service)
    {
        return response()->json(
            $service->create($request->validated()),
            201
        );
    }

    public function update(UpdateEmpleadoRequest $request, Empleado $empleado, EmpleadoService $service)
    {
        return response()->json(
            $service->update($empleado, $request->validated())
        );
    }

    public function destroy(Empleado $empleado, EmpleadoService $service)
    {
        return response()->json([
            'success' => $service->delete($empleado)
        ]);
    }

    public function toggleStatus(Empleado $empleado)
    {
        $empleado->update([
            'estatus' => !$empleado->estatus
        ]);

        return response()->json([
            'message' => 'Estado actualizado',
            'estatus' => $empleado->estatus
        ]);
    }
}
