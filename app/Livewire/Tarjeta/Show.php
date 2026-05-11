<?php

namespace App\Livewire\Tarjeta;

use App\Models\ComprobacionTarjeta;
use App\Models\Gasto;
use App\Models\GastoComprobante;
use App\Services\CFDI\CFDIService;
use App\Services\Concepto\ConceptoService;
use App\Services\Gasto\ComprobacionTarjetaService;
use App\Services\Gasto\PoliticaGastoService;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use Storage;

class Show extends Component
{
    use WithFileUploads;

    public ComprobacionTarjeta $comprobacion;

    public ?int $concepto_id   = null;
    public string $fechaGasto  = '';
    public array $archivosCfdi = [];
    public array $pdfsCfdi     = [];

    public array $conceptos = [];
    public array $gastos    = [];

    public string $motivoRechazo = '';
    public bool   $mostrandoConciliacion = false;
    public string $accionConciliacion = '';

    public function mount(ComprobacionTarjeta $comprobacion, ConceptoService $conceptoService): void
    {
        $user = auth()->user();

        if (
            $comprobacion->empleado_id !== $user->empleado->id
            && !$user->can('gastos.tarjeta.conciliar')
        ) {
            $this->redirectRoute('tarjeta.index');
            return;
        }

        $this->comprobacion = $comprobacion->load(['empleado', 'proyecto']);
        $this->conceptos    = $conceptoService->list();
        $this->sincronizarGastos();
    }

    public function procesarXmls(): void
    {
        $this->validate([
            'archivosCfdi'   => 'array',
            'archivosCfdi.*' => 'file|mimes:xml|max:2048',
            'pdfsCfdi'       => 'array',
            'pdfsCfdi.*'     => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $procesados = [];

        foreach($this->archivosCfdi as $idx => $archivo) {
            if (is_array($archivo)) {
                $procesados[] = [
                    'xml' => $archivo,
                    'pdf' => null,
                ];
                continue;
            }

            try {
                $cfdi = app(CFDIService::class)->parseTemporary($archivo);
                $uuidsActuales = collect($procesados)->pluck('uuid')->filter()->all();

                if (in_array($cfdi['uuid'], $uuidsActuales)) {
                    $procesados[] = [
                        'xml'    => $archivo,
                        'pdf'    => $this->pdfsCfdi[$idx] ?? null,
                        'uuid'   => $cfdi['uuid'],
                        'monto'  => $cfdi['total'],
                        'emisor' => $cfdi['emisor_nombre'] ?? $cfdi['emisor_rfc'] ?? '-',
                        'fecha'  => $cfdi['fecha'] ?? now()->format('Y-m-d'),
                        'error'  => 'Este XML ya fue agregado en esta carga.',
                    ];
                    continue;
                }

                $existe = GastoComprobante::where('uuid', $cfdi['uuid'])->exists();
                $errorFecha = null;

                $errorFinal = $existe ? 'Este CFDI ya fue registrado en el sistema.' : null;

                if ($errorFecha) {
                    $errorFinal = $errorFinal
                        ? $errorFinal . ' | ' . $errorFecha
                        : $errorFecha;
                }

                $procesados[] = [
                    'xml'    => $archivo,
                    'pdf'    => $this->pdfsCfdi[$idx] ?? null,
                    'uuid'   => $cfdi['uuid'],
                    'monto'  => $cfdi['total'],
                    'emisor' => $cfdi['emisor_nombre'] ?? $cfdi['emisor_rfc'] ?? '—',
                    'fecha'  => $cfdi['fecha'] ?? now()->format('Y-m-d'),
                    'error'  => $errorFinal,
                ];
            } catch (\Exception $e) {
                $procesados[] = [
                    'xml'    => $archivo,
                    'pdf'    => $this->pdfsCfdi[$idx] ?? null,
                    'uuid'   => null,
                    'monto'  => 0,
                    'emisor' => '—',
                    'fecha'  => now()->format('Y-m-d'),
                    'error'  => 'No se pudo leer el XML: ' . $e->getMessage(),
                ];
            }
        }
        $this->archivosCfdi = $procesados;
    }

    public function updatedArchivosCfdi()
    {
        $this->procesarXmls();
    }

    public function removeCfdi(int $idx): void
    {
        unset($this->archivosCfdi[$idx]);
        unset($this->pdfsCfdi[$idx]);

        $this->archivosCfdi = array_values($this->archivosCfdi);
        $this->pdfsCfdi     = array_values($this->pdfsCfdi);
    }

    public function AdjuntarPdfACfdi(int $idx): void
    {

    }

    public function guardarGastos(ComprobacionTarjetaService $service): void
    {
        $this->validate([
            'concepto_id'  => 'required|exists:conceptos,id',
            'fechaGasto'   => 'required|date',
            'archivosCfdi' => 'required|array|min:1',
            'pdfsCfdi'     => 'nullable|array',
        ], [
            'concepto_id.required'  => 'Selecciona un concepto.',
            'fechaGasto.required'   => 'La fecha del gasto es obligatoria.',
            'archivosCfdi.required' => 'Sube al menos un CFDI.',
            'archivosCfdi.min'      => 'Sube al menos un CFDI.'
        ]);

        $validos = collect($this->archivosCfdi)->filter(fn ($c) => empty($c['error']));

        if ($validos->isEmpty()) {
            $this->addError('archivosCfdi', 'No hay CFDIs válidos para guardar.');
            return;
        }

        try {
            foreach ($validos as $idx => $cfdiEntry) {
                $service->agregarGasto($this->comprobacion, [
                    'concepto_id' => $this->concepto_id,
                    'fecha_gasto' => $this->fechaGasto,
                    'monto'       => $cfdiEntry['monto'],
                    'archivo_xml' => $cfdiEntry['xml'],
                    'archivo_pdf' => $this->pdfsCfdi[$idx] ?? null,
                ], auth()->user());
            }

            $this->comprobacion = $this->comprobacion->fresh();

            $this->sincronizarGastos();

            $this->reset(['concepto_id', 'fechaGasto', 'archivosCfdi']);
            $this->resetValidation();

            Flux::toast(variant: 'success', text: count($validos) . 'gasto(s) agregado(s).');
        } catch (\Exception $e) {
            $this->dispatch('tarjetaError', message: $e->getMessage() . 'comprobación de gasto');
        }
    }

    public function eliminarGassto(int $gastoId, ComprobacionTarjetaService $service)
    {
        $gasto = Gasto::findOrFail($gastoId);

        try {
            $service->eliminarGasto($this->comprobacion, $gasto, auth()->user());
            $this->comprobacion = $this->comprobacion->fresh();

            $this->sincronizarGastos();

            Flux::toast(variant: 'success', text: 'Gasto eliminado.');
        } catch (\Exception $e) {
            $this->dispatch('tarjetaError', message: $e->getMessage());
        }
    }

    public function enviarARevision(ComprobacionTarjetaService $service): void
    {
        try {
            $service->enviarARevision($this->comprobacion, auth()->user());
            $this->comprobacion = $this->comprobacion->fresh();

            $this->modal('enviar-a-revisar')->close();
            Flux::toast(variant: 'success', text: 'Periodo enviado a revisión.');

        } catch (\Exception $e) {
            $this->dispatch('tarjetaError', messsage: $e->getMessage());
        }
    }

    public function abrirConciliacion(string $accion): void
    {
        $this->accionConciliacion    = $accion;
        $this->motivoRechazo         = '';
        $this->mostrandoConciliacion = true;
    }

    public function conciliar(ComprobacionTarjetaService $service): void
    {
        if ($this->accionConciliacion === 'rechazada') {
            $this->validate([
                'motivoRechazo' => 'required|string|min:10|max:500',
            ], [
                'motivoRechazo.required' => 'El motivo de rechazo es obligatorio.',
                'motivoRechazo.min'      => 'Mínimo 10 caracteres.',
            ]);
        }

        try {
            $service->conciliar(
                $this->comprobacion,
                auth()->user(),
                $this->accionConciliacion,
                $this->motivoRechazo ?: null,
            );

            $this->comprobacion          = $this->comprobacion->fresh();
            $this->mostrandoConciliacion = false;
            $this->sincronizarGastos();

            $label = $this->accionConciliacion === 'conciliada' ? 'conciliado' : 'rechazado';
            Flux::toast(variant: 'success', text: "Periodo {$label} correctamente.");
        } catch (\Exception $e) {
            $this->dispatch('tarjetaError', message: $e->getMessage());
        }
    }

    public function descargar(int $id, bool $isPdf): mixed
    {
        $comprobante = GastoComprobante::findOrFail($id);

        if (!$comprobante) {
            $this->dispatch('autorizacionError', message: 'Comprobante no encontrado.');
            return null;
        }

        $path = $isPdf ? $comprobante->archivo_pdf : $comprobante->archivo;

        if (!Storage::disk('private')->exists($path)) {
            $this->dispatch('autorizacionError', message: 'Archivo no encontrado en el servidor.');
            return null;
        }

        return response()->download(
            Storage::disk('private')->path($path),
            basename($path)
        );
    }

    #[On('tarjetaError')]
    public function onError(string $message): void
    {
        Flux::toast(variant: 'danger', text: $message);
    }

    private function sincronizarGastos(): void
    {
        $roleId = $this->comprobacion->empleado->user->roles->first()?->id;
        $fecha  = now();

        $gastosDb = $this->comprobacion->gastos()
            ->with(['concepto', 'comprobantes', 'excepciones'])
            ->get();

        $conceptoIds = $gastosDb->pluck('concepto_id')->unique()->all();
        $politicas   = $roleId && !empty($conceptoIds)
            ? app(PoliticaGastoService::class)->getPoliticasBulk($roleId, $conceptoIds, $fecha)
            : collect();

        $this->gastos = $gastosDb->map(function ($g) use ($politicas) {
            $politica  = $politicas->get($g->concepto_id);
            $monto     = (float) $g->monto;
            $totalComp = $g->comprobantes->sum('monto');

            return [
                'id'                    => $g->id,
                'concepto_id'           => $g->concepto_id,
                'concepto_nombre'       => $g->concepto->nombre ?? '-',
                'monto_estimado'        => $monto,
                'monto_real'            => $totalComp > 0 ? $totalComp : null,
                'limite_politica'       => $politica ? (float) $politica->monto_max : null,
                'tipo_limite_politica'  => $politica ? $politica->tipo_limite : '',
                'comprobante_requerido' => $politica
                    ? $politica->evaluarComprobacion($monto)
                    : 'ninguno',
                'estatus'               => $g->estatus,
                'fecha_gasto'           => $g->fecha_gasto?->format('Y-m-d'),
                'tiempo_excepcion'      => $g->excepciones->where('estatus', 'pendiente')->count() > 0,
                'comprobantes'          => $g->comprobantes->map(fn($c) => [
                    'id'                => $c->id,
                    'tipo'              => $c->tipo,
                    'monto'             => (float) $c->monto,
                    'uuid'              => $c->uuid,
                    'sat_status'        => $c->sat_status,
                    'validacion_manual' => $c->validacion_manual,
                    'archivo'           => $c->archivo,
                    'archivo_pdf'       => $c->archivo_pdf,
                ])->toArray(),
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.tarjeta.show');
    }
}
