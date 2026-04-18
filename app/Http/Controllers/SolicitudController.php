<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\Solicitud\SolicitudDetalleResource;
use App\Http\Resources\Solicitud\SolicitudResource;
use App\Models\Solicitud;
use App\Services\Solicitudes\SolicitudService;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    public function store(Request $request, SolicitudService $service)
    {
        $solicitud = $service->create($request->all(), $request->user());

        return ApiResponse::success($solicitud, 'Solicitud creada');
    }

    public function agregarDetalle($id, Request $request, SolicitudService $service)
    {
        $solicitud = Solicitud::findOrFail($id);

        return ApiResponse::success(
            $service->agregarDetalle($solicitud, $request->detalles),
            'Detalle agregado'
        );
    }

    public function enviar($id, Request $request, SolicitudService $service)
    {
        $solicitud = Solicitud::findOrFail($id);

        return ApiResponse::success(
            $service->enviar($solicitud, $request->user()),
            'Solicitud enviada'
        );
    }

    public function resolver($id, Request $request, SolicitudService $service)
    {
        $solicitud = Solicitud::findOrFail($id);

        return ApiResponse::success(
            $service->resolver(
                $solicitud,
                $request->accion,
                $request->motivo ?? null
            ),
            'Solicitud procesada'
        );
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Solicitud::with([
            'empleado.user:id,name',
            'proyecto:id,nombre',
        ]);

        if ($user->can('solicitudes.ver.propias')) {
            $query->where('empleado_id', $user->empleado->id);
        }

        if ($user->can('solicitudes.ver.todas')) {
            // no filtro → ve todo
        }

        if ($user->can('solicitudes.aprobar') && !$user->can('solicitudes.ver.todas')) {
            $query->where('estatus', 'Pendiente');
        }

        $query->when(
            $request->estatus,
            fn($q) =>
            $q->where('estatus', $request->estatus)
        );

        $query->when($request->search, function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('folio', 'like', "%{$request->search}%")
                    ->orWhereHas('empleado', function ($q2) use ($request) {
                        $q2->where('nombre_completo', 'like', "%{$request->search}%");
                    });
            });
        });

        $query->when(
            $request->fecha_inicio,
            fn($q) =>
            $q->whereDate('fecha_inicio', '>=', $request->fecha_inicio)
        );

        $query->when(
            $request->fecha_fin,
            fn($q) =>
            $q->whereDate('fecha_fin', '<=', $request->fecha_fin)
        );

        $query->latest();

        $solicitudes = $query->paginate(15);

        return ApiResponse::success([
            'data' => SolicitudResource::collection($solicitudes),
            'meta' => [
                'total' => $solicitudes->total(),
                'pendientes' => Solicitud::where('estatus', 'Pendiente')->count(),
                'aprobadas' => Solicitud::where('estatus', 'Autorizado')->count(),
            ]
        ]);
    }

    public function show($id, Request $request)
    {
        $solicitud = Solicitud::with([
            'empleado.user:id,name',
            'area:id,nombre',
            'proyecto:id,nombre',

            'detalles.concepto:id,nombre',

            'gastos.concepto:id,nombre',
            'gastos.excepciones',
            'gastos.solicitud.empleado.user:id,name',
        ])->findOrFail($id);

        return ApiResponse::success(
            new SolicitudDetalleResource($solicitud),
            'Detalle de solicitud'
        );
    }

    public function cancelar(Solicitud $solicitud, Request $request, SolicitudService $service)
    {
        return ApiResponse::success(
            $service->cancelar($solicitud, $request->user(), $request->motivo),
            'Solicitud cancelada'
        );
    }

    public function reabrir(Solicitud $solicitud, Request $request, SolicitudService $service)
    {
        return ApiResponse::success(
            $service->reabrir($solicitud, $request->user()),
            'Solicitud reabierta'
        );
    }

    public function aprobar(
        Solicitud $solicitud,
        Request $request,
        SolicitudService $service
    ) {
        return ApiResponse::success(
            $service->aprobar($solicitud, $request->user()),
            'Solicitud aprobada'
        );
    }
}
