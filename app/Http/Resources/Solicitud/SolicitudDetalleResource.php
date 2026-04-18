<?php

namespace App\Http\Resources\Solicitud;

use App\Models\GastoAuditoria;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudDetalleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 🔹 INFO GENERAL
            'id' => $this->id,
            'folio' => $this->folio,
            'estatus' => $this->estatus,

            'empleado' => [
                'nombre' => $this->empleado->nombre_completo,
                'usuario' => $this->empleado->user->name,
            ],

            'area' => $this->area?->nombre,
            'proyecto' => $this->proyecto?->nombre,

            'fechas' => [
                'inicio' => $this->fecha_inicio,
                'fin' => $this->fecha_fin,
            ],

            'monto_total' => $this->monto_total,

            'acciones' => $this->accionesDisponibles($request->user()),

            // 🔹 DETALLES (LO QUE SOLICITÓ)
            'conceptos' => $this->detalles->map(fn($d) => [
                'id' => $d->id,
                'concepto' => $d->concepto->nombre,
                'monto_estimado' => $d->monto_estimado,
            ]),

            // 🔹 GASTOS (LO REAL)
            'gastos' => $this->gastos->map(fn($g) => [
                'id' => $g->id,
                'concepto' => $g->concepto->nombre,
                'monto' => $g->monto,
                'estatus' => $g->estatus,

                'excepciones' => $g->excepciones->map(fn($e) => [
                    'nivel' => $e->nivel,
                    'estatus' => $e->estatus,
                ])
            ]),

            'timeline' => GastoAuditoria::whereIn(
                'gasto_id',
                $this->gastos->pluck('id')
            )->with('actor:id,name')
                ->orderBy('created_at')
                ->get(),

            // 🔹 RESUMEN
            'resumen' => [
                'total_estimado' => $this->detalles->sum('monto_estimado'),
                'total_real' => $this->gastos->sum('monto'),
                'diferencia' => $this->gastos->sum('monto') - $this->detalles->sum('monto_estimado'),
            ],
        ];
    }

    protected function accionesDisponibles($user)
    {
        $acciones = [];

        // 🔹 EDITAR (solo dueño y en borrador)
        if (
            $this->estatus === 'Borrador' &&
            $user->can('solicitudes.editar') &&
            $user->empleado?->id === $this->empleado_id
        ) {
            $acciones[] = 'editar';
            $acciones[] = 'agregar_detalle';
        }

        // 🔹 ENVIAR
        if (
            $this->estatus === 'Borrador' &&
            $user->can('solicitudes.enviar') &&
            $this->detalles->count() > 0
        ) {
            $acciones[] = 'enviar';
        }

        // 🔹 APROBAR / RECHAZAR (gerente)
        if (
            $this->estatus === 'Pendiente' &&
            $user->can('solicitudes.aprobar')
        ) {
            $acciones[] = 'aprobar';
            $acciones[] = 'rechazar';
        }

        // cancelar
        if (
            in_array($this->estatus, ['Borrador', 'Pendiente']) &&
            $user->can('solicitudes.eliminar')
        ) {
            $acciones[] = 'cancelar';
        }

        // reabrir
        if (
            in_array($this->estatus, ['Rechazado', 'Cancelado']) &&
            $user->empleado?->id === $this->empleado_id
        ) {
            $acciones[] = 'reabrir';
        }

        return $acciones;
    }
}
