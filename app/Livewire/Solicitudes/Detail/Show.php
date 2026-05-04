<?php

namespace App\Livewire\Solicitudes\Detail;

use App\Models\Solicitud;
use App\Models\SolicitudAuditoria;
use App\Models\SolicitudDetalle;
use App\Services\Concepto\ConceptoService;
use App\Services\Gasto\PoliticaGastoService;
use App\Services\Solicitudes\SolicitudAprobacionService;
use App\Services\Solicitudes\SolicitudService;
use Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public Solicitud $solicitud;
    public int $stepActual = 1;

    public ?int $concepto_id = null;
    public string $monto = '';

    public array $conceptos = [];
    public array $detalles = [];
    public array $aprobadores        = [];
    public int   $aprobacionesMinimo = 2;
    public int   $aprobacionesTotal  = 0;
    public int   $aprobacionesFaltan = 0;

    public int $kpi_ok           = 0;
    public int $kpi_limite       = 0;
    public int $kpi_excedido     = 0;
    public int $kpi_sin_politica = 0;
    public float $total          = 0.00;

    public array $justificaciones          = [];
    public bool  $mostrandoJustificaciones = false;

    public function mount(Solicitud $solicitud, ConceptoService $conceptoService): void
    {
        $this->solicitud = $solicitud->load([
            'empleado.user.roles',
            'proyecto',
            'detalles.concepto'
        ]);

        $role_id = $this->solicitud->empleado->user->roles->first()?->id;
        $this->conceptos = $conceptoService->list($role_id);

        $this->sincronizarDetalles();
        $this->calcularKpis();
        $this->calcularStep();
        $this->cargarAprobadores();
    }

    public function agregarDetalle(SolicitudService $service): void
    {
        if($this->solicitud->estatus !== 'Borrador') {
            $this->addError('concepto_id', 'Solo puedes agregar conceptos en borrador');
            return;
        }

        $this->validate([
            'concepto_id' => 'required|exists:conceptos,id',
            'monto'       => 'required|numeric|min:0.01',
        ], [
            'concepto_id.required' => 'Selecciona un concepto.',
            'monto.required'       => 'El monto es obligatorio.',
            'monto.numeric'        => 'El monto debe ser un númerico.',
            'monto.min'            => 'El monto debe ser mayor a cero.',
        ]);

        $service->agregarDetalle(
            $this->solicitud,
            [[ 'concepto_id' => $this->concepto_id, 'monto_estimado' => $this->monto ]],
            auth()->user()
        );

        $this->solicitud = $this->solicitud->fresh(['detalles.concepto']);
        $this->sincronizarDetalles();
        $this->calcularKpis();

        $this->reset(['concepto_id', 'monto']);
        $this->resetValidation();
    }

    public function eliminarDetalle(int $detalleId, SolicitudService $service): void
    {
        if ($this->solicitud->estatus !== 'Borrador') {
            return;
        }

        SolicitudDetalle::where('id', $detalleId)
            ->where('solicitud_id', $this->solicitud->id)
            ->delete();

        $total = SolicitudDetalle::where('solicitud_id', $this->solicitud->id)
            ->sum('monto_estimado');

        $this->solicitud->update(['monto_total' => $total]);
        $this->solicitud = $this->solicitud->fresh(['detalles.concepto']);
        $this->sincronizarDetalles();
        $this->calcularKpis();
    }

    public function enviar(SolicitudService $service): void
    {
        $excedidosSinJustificar = collect($this->detalles)
            ->filter(fn($d) => $d['semaforo'] === 'excedido' && empty($d['justificacion_exceso']));

        if ($excedidosSinJustificar->isNotEmpty()) {
            $this->dispatch('abrirJustificaciones');
            return;
        }

        try {
            $service->enviar($this->solicitud, auth()->user());
            $this->solicitud = $this->solicitud->fresh();

            $this->calcularStep();
            $this->dispatch('solicitudEnviada', message: 'Solicitud enviada a revisión.');
        } catch(\App\Exceptions\Solicitudes\SolicitudBloqueadaException $e) {
            $this->dispatch('solicitudError', message: $e->getMessage());
        } catch(\Exception $e) {
            $this->dispatch('solicitudError', message: $e->getMessage());
        }
    }

    #[On('solicitudEnviada')]
    public function onAutorizacionResuelta(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    #[On('solicitudError')]
    public function onAutorizacionError(string $message): void
    {
        Flux::toast(variant: 'danger', text: $message);
    }

    #[On('abrirJustificaciones')]
    public function abrirJustificaciones(): void
    {
        $this->justificaciones = collect($this->detalles)
            ->filter(fn($d) => $d['semaforo'] === 'excedido')
            ->mapWithKeys(fn($d) => [$d['id'] => $d['justificacion_exceso'] ?? ''])
            ->toArray();

        $this->mostrandoJustificaciones = true;
    }

    public function guardarJustificacionesYEnviar(SolicitudService $service): void
    {
        $rules = collect($this->justificaciones)
            ->mapWithKeys(fn($v, $id) => ["justificaciones.{$id}" => 'required|string|min:10|max:500'])
            ->toArray();

        $this->validate($rules, [
            'justificaciones.*.required' => 'La justificación es obligatoria.',
            'justificaciones.*.min'      => 'Mínimo 10 caracteres.',
        ]);

        try {
            $service->enviarJustificaciones($this->solicitud, $this->justificaciones);

            $this->mostrandoJustificaciones = false;
            $this->solicitud = $this->solicitud->fresh(['detalles.concepto']);
            $this->sincronizarDetalles();

            $service->enviar($this->solicitud, auth()->user());
            $this->solicitud = $this->solicitud->fresh();
            $this->calcularStep();

            $this->dispatch('solicitudEnviada', message: 'Solicitud enviada con justificaciones.');
        } catch(\App\Exceptions\Solicitudes\SolicitudBloqueadaException $e) {
            $this->dispatch('solicitudError', message: $e->getMessage());
        } catch(\Exception $e) {
            $this->dispatch('solicitudError', message: $e->getMessage());
        }
    }

    private function sincronizarDetalles(): void
    {
        $role_id = $this->solicitud->empleado->user->roles->first()?->id;
        $fecha   = now();

        $conceptoIds = $this->solicitud->detalles->pluck('concepto_id')->unique()->all();

        $politicas = $role_id && !empty($conceptoIds)
            ? app(PoliticaGastoService::class)->getPoliticasBulk($role_id, $conceptoIds, $fecha)
            : collect();

        $this->detalles = $this->solicitud->detalles
            ->map(function ($d) use ($politicas) {
                $politica = $politicas->get($d->concepto_id);
                $monto    = (float) $d->monto_estimado;

                $semaforo = $this->calcularSemaforo($politica, $monto);
                $comprobanteRequerido = $politica
                    ? $politica->evaluarComprobacion($monto)
                    : 'ninguno';

                return [
                    'id'                   => $d->id,
                    'concepto_id'          => $d->concepto_id,
                    'concepto_nombre'      => $d->concepto->nombre ?? '—',
                    'tipo_aplicacion'      => $d->concepto->tipo_aplicacion ?? '—',
                    'monto_estimado'       => $monto,
                    'limite_politica'      => $politica ? (float) $politica->monto_max : null,
                    'comprobante_requerido'=> $comprobanteRequerido,
                    'semaforo'             => $semaforo,
                ];
            })->toArray();
    }

    private function calcularSemaforo(?object $politica, float $monto): string
    {
        if (!$politica) {
            return 'sin_politica';
        }

        $max = (float) $politica->monto_max;

        if ($monto > $max) {
            return 'excedido';
        }

        if ($monto >= $max * 0.9) {
            return 'limite';
        }

        return 'ok';
    }

    private function cargarAprobadores(): void
    {
        $resultado = app(SolicitudAprobacionService::class)
            ->aprobadoresDe($this->solicitud);

        $this->aprobadores        = $resultado['aprobadores'];
        $this->aprobacionesMinimo = $resultado['minimo'];
        $this->aprobacionesTotal  = $resultado['aprobadas'];
        $this->aprobacionesFaltan = $resultado['faltan'];
    }

    private function calcularKpis(): void
    {
        $this->kpi_ok           = collect($this->detalles)->where('semaforo', 'ok')->count();
        $this->kpi_limite       = collect($this->detalles)->where('semaforo', 'limite')->count();
        $this->kpi_excedido     = collect($this->detalles)->where('semaforo', 'excedido')->count();
        $this->kpi_sin_politica = collect($this->detalles)->where('semaforo', 'sin_politica')->count();
        $this->total            = collect($this->detalles)->sum('monto_estimado');
    }

    private function calcularStep(): void
    {
        $this->stepActual = match($this->solicitud->estatus) {
            'Borrador'              => 1,
            'Pendiente'             => 2,
            'Autorizado', 'Comprobado' => 3,
            default                 => 1,
        };
    }

    public function render()
    {
        return view('livewire.solicitudes.detail.show');
    }
}
