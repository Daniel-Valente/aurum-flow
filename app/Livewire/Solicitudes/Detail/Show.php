<?php

namespace App\Livewire\Solicitudes\Detail;

use App\Models\Gasto;
use App\Models\GastoComprobante;
use App\Models\GastoExcepcion;
use App\Models\Solicitud;
use App\Models\SolicitudDetalle;
use App\Services\CFDI\CFDIService;
use App\Services\Concepto\ConceptoService;
use App\Services\Gasto\GastoService;
use App\Services\Gasto\PoliticaGastoService;
use App\Services\Solicitudes\SolicitudAprobacionService;
use App\Services\Solicitudes\SolicitudService;
use Carbon\Carbon;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

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

    public array  $gastos            = [];
    public ?int   $gastoActivo       = null;
    public ?int   $gastoConExcepcion = null;
    public string $tipoComprobante   = '';
    public string $fechaGastoReal    = '';

    public array $archivosComprobantes = [];
    public array $montosComprobantes   = [];
    public array $archivosCfdi         = [];
    public array $pdfsCfdi             = [];

    public ?int $editandoDetalle = null;
    public string $editandoMonto = '';

    public string $justificacionExcepcion = '';

    public function mount(Solicitud $solicitud, ConceptoService $conceptoService): void
    {
        $this->authorize('ver', $solicitud);

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

        if ($this->stepActual === 3) {
            $this->sincronizarGastos();
        }
    }

    public function boot(): void
    {
        if ($this->solicitud && auth()->check()) {
            if (!auth()->user()->can('ver', $this->solicitud)) {
                $this->redirectRoute('solicitudes.index');
            }
        }
    }

    #[On('comprobanteValidado')]
    public function onComprobanteValidado(string $message): void
    {
        $this->sincronizarGastos();
        $this->recalcularComprobacion();

        Flux::toast(variant: 'success', text: $message);
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

    public function editarDetalle(int $detalleId): void
    {
        $detalle = SolicitudDetalle::findOrFail($detalleId);

        $this->editandoDetalle = $detalle->id;
        $this->editandoMonto = (string) $detalle->monto_estimado;
    }

    public function guardarDetalle(int $detalleId): void
    {
        $this->validate([
            'editandoMonto' => 'required|numeric|min:0.01',
        ]);

        $detalle = SolicitudDetalle::where('id', $detalleId)
            ->where('solicitud_id', $this->solicitud->id)
            ->firstOrFail();

        $detalle->update([
            'monto_estimado' => $this->editandoMonto,
        ]);

        // recalcular total solicitud
        $total = SolicitudDetalle::where('solicitud_id', $this->solicitud->id)
            ->sum('monto_estimado');

        $this->solicitud->update([
            'monto_total' => $total,
        ]);

        $this->solicitud = $this->solicitud->fresh(['detalles.concepto']);

        $this->sincronizarDetalles();
        $this->calcularKpis();

        $this->editandoDetalle = null;
        $this->editandoMonto = '';

        Flux::toast(
            variant: 'success',
            text: 'Monto actualizado.'
        );
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
            $this->dispatch('autorizacionError', message: $e->getMessage());
        } catch(\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    #[On('solicitudEnviada')]
    public function onAutorizacionResuelta(string $message): void
    {
        Flux::toast(variant: 'success', text: $message);
    }

    #[On('autorizacionError')]
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
            $this->dispatch('autorizacionError', message: $e->getMessage());
        } catch(\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    public function abrirComprobacion(int $gastoId): void
    {
        $this->gastoActivo           = $gastoId;
        $this->tipoComprobante       = '';
        $this->fechaGastoReal        = now()->format('Y-m-d');
        $this->archivosComprobantes  = [];
        $this->montosComprobantes    = [];
        $this->archivosCfdi          = [];
        $this->pdfsCfdi              = [];
        $this->resetValidation();
    }

    public function cerrarComprobacion(): void
    {
        $this->gastoActivo = null;
        $this->resetValidation();
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

                /*if (!empty($this->fechaGastoReal) && !empty($cfdi['fecha'])) {
                    $errorFecha = $this->validarRangoFechaCfdi(
                        $cfdi['fecha'],
                        $this->fechaGastoReal
                    );
                }*/

                $errorFinal = $existe ? 'Este CFDI ya fue registrado en el sistema.' : null;

                // Si ya hay error de duplicado, concatena
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

    public function AdjuntarPdfACfdi(int $idx): void
    {

    }

    public function guardarComprobacion(GastoService $service): void
    {
        $rules = [
            'tipoComprobante' => 'required|in:factura,pdf,sin_comprobante',
            'fechaGastoReal'  => 'required|date',
        ];

        if ($this->tipoComprobante === 'pdf') {
            $rules['archivosComprobantes']   = 'required|array|min:1';
            $rules['archivosComprobantes.*'] = 'file|mimes:pdf,jpg,jepg,png|max:5120';
            $rules['montosComprobantes']     = 'required|array|min:1';
            $rules['montosComprobantes.*']     = 'required|numeric|min:0.01';
        }

        $this->validate($rules, [
            'tipoComprobante.required'          => 'Selecciona el tipo de comprobante.',
            'fechaGastoReal.required'           => 'La fecha es obligatoria.',
            'archivosComprobantes.required'     => 'Sube al menos un ticket.',
            'archivosComprobantes.min'          => 'Sube al menos un ticket.',
            'archivosComprobantes.*.file'       => 'Cada elemento debe ser un archivo.',
            'archivosComprobantes.*.mimes'      => 'Solo PDF, JPG o PNG.',
            'montosComprobantes.*.required'     => 'Cada ticket necesita su monto.',
            'montosComprobantes.*.numeric'      => 'El monto debe ser número.',
            'montosComprobantes.*.min'          => 'El monto debe ser mayor a cero.',
        ]);

        $gasto = Gasto::findOrFail($this->gastoActivo);

        try {
            if ($this->tipoComprobante === 'factura') {
                $validos = collect($this->archivosCfdi)->filter(fn($c) => empty($c['error']));

                if($validos->isEmpty()) {
                    $this->addError('archivosCfdi', 'No hay CFDIs válidos para guardar.');
                    return;
                }

                foreach ($validos as $idx => $cfdiEntry) {
                    $data = [
                        'tipo'             => 'factura',
                        'monto'            => 0,             // lo sobreescribe el parse
                        'fecha_gasto'      => $this->fechaGastoReal,
                        'archivo_pdf_cfdi' => $this->pdfsCfdi[$idx] ?? null,
                    ];

                    $service->subirComprobante($gasto, auth()->user(), $cfdiEntry['xml'], $data);
                }
            } elseif ($this->tipoComprobante === 'pdf') {
                foreach($this->archivosComprobantes as $idx => $archivo) {
                    $monto = (float) ($this->montosComprobantes[$idx] ?? 0);

                    $data = [
                        'tipo'        => 'pdf',
                        'monto'       => $monto,
                        'fecha_gasto' => $this->fechaGastoReal
                    ];

                    $service->subirComprobante($gasto, auth()->user(), $archivo, $data);
                }
            } elseif ($this->tipoComprobante === 'sin_comprobante') {
                $gasto->update(['fecha_gasto' => $this->fechaGastoReal, 'estatus' => 'Comprobado']);
            }

            $gasto = $gasto->fresh();

            if(!$gasto->fecha_gasto) {
                $gasto->update(['fecha_gasto' => $this->fechaGastoReal]);
            }

            if ($gasto->estatus === 'excepcion') {
                $this->gastoConExcepcion = $gasto->id;
                $this->sincronizarGastos();
                $this->recalcularComprobacion();
                $this->reset(['tipoComprobante', 'archivosComprobantes', 'montosComprobantes']);
                $this->fechaGastoReal = now()->format('Y-m-d');

                return;
            }

            $this->sincronizarGastos();
            $this->recalcularComprobacion();
            $this->reset(['tipoComprobante', 'archivosComprobantes', 'montosComprobantes']);
            $this->fechaGastoReal = now()->format('Y-m-d');

            Flux::toast(variant: 'success', text: 'Comprobante agregado.');

        } catch (\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    public function guardarJustificacionExcepcion(): void
    {
        $this->validate([
            'justificacionExcepcion' => 'required|string|min:10|max:500',
        ], [
            'justificacionExcepcion.required' => 'La justificación es obligatoria.',
            'justificacionExcepcion.min'      => 'Mínimo 10 caracteres.',
        ]);

        GastoExcepcion::where('gasto_id', $this->gastoConExcepcion)
            ->where('estatus', 'pendiente')
            ->update(['comentario' => $this->justificacionExcepcion]);

        $this->gastoConExcepcion      = null;
        $this->justificacionExcepcion = '';
        $this->gastoActivo            = null;
        $this->sincronizarGastos();
        $this->recalcularComprobacion();

        Flux::toast(variant: 'warning', text: 'El gasto quedó en revisión de excepción con tu justificación.');
    }

    public function removeArchivo(int $idx): void
    {
        unset($this->archivosComprobantes[$idx]);
        unset($this->montosComprobantes[$idx]);

        $this->archivosComprobantes = array_values($this->archivosComprobantes);
        $this->montosComprobantes   = array_values($this->montosComprobantes);
    }

    public function removeCfdi(int $idx): void
    {
        unset($this->archivosCfdi[$idx]);
        unset($this->pdfsCfdi[$idx]);

        $this->archivosCfdi = array_values($this->archivosCfdi);
        $this->pdfsCfdi     = array_values($this->pdfsCfdi);
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
                    'comprobante_requerido'=> $comprobanteRequerido,
                    'semaforo'             => $semaforo,
                ];
            })->toArray();
    }

    private function sincronizarGastos(): void
    {
        $roleId = $this->solicitud->empleado->user->roles->first()?->id;
        $fecha  = now();

        $gastosDb = $this->solicitud->gastos()
            ->with(['concepto', 'comprobantes', 'excepciones'])
            ->get();

        $conceptoIds = $gastosDb->pluck('concepto_id')->unique()->all();

        $politicas = $roleId && !empty($conceptoIds)
            ? app(PoliticaGastoService::class)->getPoliticasBulk($roleId, $conceptoIds, $fecha)
            : collect();

        $this->gastos = $gastosDb->map(function ($g) use ($politicas) {
            $politica     = $politicas->get($g->concepto_id);
            $monto        = (float) $g->monto;
            $totalComp    = $g->comprobantes->sum('monto');

            return [
                'id'                    => $g->id,
                'concepto_id'           => $g->concepto_id,
                'concepto_nombre'       => $g->concepto->nombre ?? '—',
                'monto_estimado'        => $monto,
                'monto_real'            => $totalComp > 0 ? $totalComp : null,
                'limite_politica'       => $politica ? (float) $politica->monto_max : null,
                'tipo_limite_politica'  => $politica ? $politica->tipo_limite : '',
                'tipos_permitidos'      => $this->tiposPermitidosByPolitica($politica),
                'comprobante_requerido' => $politica
                    ? $politica->evaluarComprobacion($monto)
                    : 'ninguno',
                'estatus'               => $g->estatus,
                'fecha_gasto'           => $g->fecha_gasto?->format('Y-m-d'),
                'tiene_excepcion'       => $g->excepciones->where('estatus', 'pendiente')->count() > 0,
                'comprobantes'          => $g->comprobantes->map(fn($c) => [
                    'id'               => $c->id,
                    'tipo'             => $c->tipo,
                    'monto'            => (float) $c->monto,
                    'uuid'             => $c->uuid,
                    'sat_status'       => $c->sat_status,
                    'validacion_manual'=> $c->validacion_manual,
                    'archivo'           => $c->archivo,
                    'archivo_pdf'       => $c->archivo_pdf,
                ])->toArray(),
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

        if ($this->stepActual === 3) {
            $this->sincronizarGastos();
        }
    }

    private function recalcularComprobacion(): void
    {
        $solicitudAntes = $this->solicitud->estatus;

        $this->solicitud = $this->solicitud->fresh(['gastos.comprobantes']);
        $this->sincronizarGastos();

        // ✅ Detectar si se completó automáticamente
        if ($solicitudAntes === 'Autorizado' && $this->solicitud->estatus === 'Comprobado') {
            Flux::toast(
                variant: 'success',
                heading: '🎉 ¡Solicitud completada!',
                text: 'Todos los gastos han sido comprobados correctamente.',
                duration: 8000
            );
        }
    }

    private function tiposPermitidosByPolitica(?object $politica): array
    {
        if (!$politica) {
            return ['sin_comprobante', 'pdf', 'factura'];
        }

        $libre = $politica->monto_libre;
        $comp  = $politica->monto_comprobante;
        $fac   = $politica->monto_factura;

        $sinTramos = $libre === null && $comp === null && $fac === null;

        if ($sinTramos) {
            return ['sin_comprobante'];
        }

        $tipos = [];

        if ($libre !== null) {
            $tipos[] = 'sin_comprobante';
        }

        if ($comp !== null) {
            $tipos[] = 'pdf';
        }

        if ($fac !== null) {
            $tipos[] = 'factura';
        }

        if ($libre !== null && $comp === null && $fac === null) {
            $tipos[] = 'pdf';
        }

        return array_unique($tipos);
    }

    private function validarRangoFechaCfdi(string $fechaCfdi, string $fechaGastoReal): ?string
    {
        try {
            $cfdi = Carbon::parse($fechaCfdi)->startOfDay();
            $gasto = Carbon::parse($fechaGastoReal)->startOfDay();

            $diasAntes = config('cfdi.fecha_validacion.dias_antes', 3);
            $diasDespues = config('cfdi.fecha_validacion.dias_despues', 10);

            $min = $gasto->copy()->subDays($diasAntes);
            $max = $gasto->copy()->addDays($diasDespues);

            if ($cfdi->lt($min) || $cfdi->gt($max)) {
                return "La fecha del CFDI ({$cfdi->toDateString()}) está fuera del rango permitido ({$min->toDateString()} - {$max->toDateString()})";
            }

            return null;

        } catch (\Throwable $e) {
            return 'No se pudo validar la fecha del CFDI.';
        }
    }

    public function render()
    {
        return view('livewire.solicitudes.detail.show');
    }
}
