<?php

namespace App\Livewire\Autorizaciones;

use App\Models\Solicitud;
use App\Models\SolicitudDetalle;
use App\Services\Gasto\PoliticaGastoService;
use App\Services\Solicitudes\SolicitudAprobacionService;
use Livewire\Attributes\On;
use Livewire\Component;

class DetailModal extends Component
{
    public ?Solicitud $solicitud = null;
    public array $detalles = [];

    public bool $confirmandoRechazo = false;
    public string $motivo_rechazo = '';

    #[On('openAutorizacionDetail')]
    public function open(int $id): void
    {
        $this->solicitud = Solicitud::with([
            'empleado.user.roles',
            'proyecto',
            'detalles.concepto'
        ])->findOrFail($id);

        $this->sincronizarDetalles();

        $this->modal('autorizacion-detail')->show();
    }

    public function close(): void
    {
        $this->solicitud = null;
        $this->modal('autorizacion-detail')->close();
    }

    public function iniciarRechazo(): void
    {
        $this->confirmandoRechazo = true;
        $this->motivo_rechazo     = '';

        $this->resetValidation();
    }

    public function cancelarRechazo(): void
    {
        $this->confirmandoRechazo = false;
        $this->motivo_rechazo     = '';
    }

    public function aprobar(SolicitudAprobacionService $service): void
    {
        try {
            $service->resolver($this->solicitud, auth()->user(), 'aprobado');

            $this->modal('autorizacion-detail')->close();

            $this->dispatch('autorizacionResuelta', message: 'Solicitud aprobada.');

        } catch(\App\Exceptions\Solicitudes\AutoAprobacionException $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        } catch(\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    public function rechazar(SolicitudAprobacionService $service): void
    {
        $this->validate([
            'motivo_rechazo' => 'required|string|min:10|max:500'
        ], messages: [
            'motivo_rechazo.required' => 'El motivo de rechazo es obligatorio.',
            'motivo_rechazo.min'      => 'El motivo debe tener al menos 10 caracteres',
            'motivo_rechazo.max'    => 'El motivo de rechazo no puede exceder 500 caracteres.'
        ]);

        try {
            $service->resolver($this->solicitud, auth()->user(), 'rechazado', $this->motivo_rechazo);
            $this->confirmandoRechazo = false;

            $this->modal('autorizacion-detail')->close();
            $this->dispatch('autorizacionResuelta', message: 'Solicitud rechazada.');
        } catch (\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.autorizaciones.detail-modal');
    }

    private function sincronizarDetalles(): void
    {
        $role_id = $this->solicitud->empleado->user->roles->first()?->id;
        $fecha   = now();

        $conceptoIds = $this->solicitud->detalles->pluck('concepto_id')->unique()->all();
        $politicas = $role_id && !empty($conceptoIds)
            ? App(PoliticaGastoService::class)->getPoliticasBulk($role_id, $conceptoIds, $fecha)
            : collect();

        $this->detalles = $this->solicitud->detalles
            ->map(function ($d) use ($politicas) {
                $politica = $politicas->get($d->concepto_id);
                $monto    = (float) $d->monto_estimado;

                $semaforo = $this->calcularSemaforo($politica, $this->solicitud, $monto);
                $comprobanteRequerido = $politica
                    ? $politica->evaluarComprobacion($monto)
                    : 'ninguno';

                return [
                    'id'                   => $d->id,
                    'concepto_id'          => $d->concepto_id,
                    'concepto_nombre'      => $d->concepto->nombre ?? '—',
                    'monto_estimado'       => $monto,
                    'limite_politica'      => $politica ? (float) $politica->monto_max : null,
                    'tipo_limite_politica'  => $politica ? $politica->tipo_limite : '',
                    'permite_excepcion'           => $politica?->permite_excepcion ?? false,
                    'comprobante_requerido'=> $comprobanteRequerido,
                    'semaforo'             => $semaforo,
                    'requiere_extension_tarjeta' => (bool) $d->requiere_extension_tarjeta,
                    'monto_extension_tarjeta'    => $d->monto_extension_tarjeta
                        ? (float) $d->monto_extension_tarjeta
                        : null,
                ];
            })->toArray();
    }

    private function calcularSemaforo(?object $politica, ?object $solicitud, float $monto): string
    {
        if (!$politica) {
            return 'sin_politica';
        }

        $max = (float) $politica->monto_max;

        if (
            $politica->tipo_limite === 'Diario' &&
            $solicitud?->fecha_inicio &&
            $solicitud?->fecha_fin
        ) {
            $duracion = $solicitud->fecha_inicio
                ->diffInDays($solicitud->fecha_fin) + 1;

            $max *= $duracion;
        }

        if ($monto > $max) {
            return 'excedido';
        }

        if ($monto >= $max * 0.9) {
            return 'limite';
        }

        return 'ok';
    }
}
