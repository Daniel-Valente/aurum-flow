<?php

namespace App\Livewire\Solicitudes\Detail;

use App\Models\ComprobacionTarjeta;
use App\Models\ConfiguracionEmpresa;
use App\Models\Empleado;
use App\Models\Gasto;
use App\Models\GastoCompartido;
use App\Models\GastoComprobante;
use App\Models\GastoExcepcion;
use App\Models\Solicitud;
use App\Models\SolicitudDetalle;
use App\Services\CFDI\CFDIService;
use App\Services\Concepto\ConceptoService;
use App\Services\Empleado\EmpleadoService;
use App\Services\Gasto\GastoCompartidoService;
use App\Services\Gasto\GastoService;
use App\Services\Gasto\PoliticaGastoService;
use App\Services\Gasto\ValidadorGastosService;
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

    public bool    $marcandoCompartido       = false;
    public ?int    $gastoCompartidoActivo    = null;
    public string  $tipoCompartido           = 'empleado';
    public ?int    $empleadoReceptorId       = null;
    public string  $clienteDescripcion       = '';
    public string  $montoCompartido          = '';

    public array   $compartidosPendientes    = [];
    public ?int    $compartidoParaVincular   = null;

    public array   $extensionTarjeta         = [];
    public bool    $tieneTarjetaCorporativa  = false;

    public array $empleadosDisponibles = [];

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
        $this->tieneTarjetaCorporativa = (bool) $this->solicitud->empleado->tarjeta_credito_corporativa_asignada;

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
        $excedidosSinResolver = collect($this->detalles)
            ->filter(fn($d) =>
                $d['semaforo'] === 'excedido'
                && empty($d['justificacion_exceso'])
                && !$d['requiere_extension_tarjeta']
            );

        if ($excedidosSinResolver->isNotEmpty()) {
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
            ->filter(fn($d) =>
                $d['semaforo'] === 'excedido'
                && empty($d['justificacion_exceso'])
                && !$d['requiere_extension_tarjeta']
            )
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
        $this->gastoActivo     = $gastoId;
        $this->tipoComprobante = '';

        $gasto = collect($this->gastos)->firstWhere('id', $gastoId);
        $montoEstimado = $gasto['monto_estimado'] ?? 0;

        $roleId = $this->solicitud->empleado->user->roles->first()?->id;
        $politica = app(PoliticaGastoService::class)
            ->getPoliticaAplicable($roleId, $gasto['concepto_id'], now());

        if ($politica) {
            $nivel = $politica->evaluarComprobacion($montoEstimado);

            $this->tipoComprobante = match($nivel) {
                'cfdi'    => 'factura',
                'ticket'  => 'pdf',
                'ninguno' => 'sin_comprobante',
                default   => '',
            };
        }

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

                if (!empty($this->fechaGastoReal) && !empty($cfdi['fecha'])) {
                    $gasto = collect($this->gastos)->firstWhere('id', $this->gastoActivo);
                    $errorFecha = app(ValidadorGastosService::class)->validarRangoFechaCfdi(
                        $cfdi['fecha'],
                        $this->fechaGastoReal,
                        $gasto
                    );
                }

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
        $gastoData = collect($this->gastos)->firstWhere('id', $gasto->id);
        $tieneExtension = !empty($gastoData['extension_tarjeta']);

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
                        'monto'            => 0,
                        'fecha_gasto'      => $this->fechaGastoReal,
                        'archivo_pdf_cfdi' => $this->pdfsCfdi[$idx] ?? null,
                        'monto_override'   => $tieneExtension ? $gasto->monto : null,
                        'cfdi_compartido'  => $tieneExtension,
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

    public function abrirCompartido(int $gastoId): void
    {
        $this->gastoCompartidoActivo = $gastoId;
        $this->marcandoCompartido    = true;
        $this->tipoCompartido        = 'empleado';
        $this->empleadoReceptorId    = null;
        $this->clienteDescripcion    = '';
        $this->montoCompartido       = '';

        $this->empleadosDisponibles = Empleado::query()
            ->where('estatus', true)
            ->where('id', '!=', $this->solicitud->empleado_id)
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo'])
            ->toArray();
    }

    public function guardarCompartido(GastoCompartidoService $service): void
    {
        $this->validate([
            'montoCompartido'      => 'required|numeric|min:0.01',
            'tipoCompartido'       => 'required|in:empleado,cliente',
            'empleadoReceptorId'   => 'required_if:tipoCompartido,empleado|exists:empleados,id',
            'clienteDescripcion'   => 'required_if:tipoCompartido,cliente|nullable|string|max:200',
        ], [
            'montoCompartido.required'    => 'Indica el monto que comparte.',
            'empleadoReceptorId.required' => 'Selecciona el empleado receptor.',
            'clienteDescripcion.required' => 'Describe el cliente o invitado.',
        ]);

        $gasto = Gasto::findOrFail($this->gastoCompartidoActivo);

        try {
            $service->marcarCompartido(
                $gasto,
                $this->tipoCompartido,
                (float) $this->montoCompartido,
                $this->tipoCompartido === 'empleado' ? $this->empleadoReceptorId : null,
                $this->tipoCompartido === 'cliente'  ? $this->clienteDescripcion  : null,
            );

            $this->marcandoCompartido    = false;
            $this->gastoCompartidoActivo = null;
            $this->sincronizarGastos();
            $this->cargarCompartidosPendientes();

            Flux::toast(variant: 'success', text: 'Gasto marcado como compartido.');
        } catch (\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    public function cargarCompartidosPendientes(): void
    {
        $empleadoId = $this->solicitud->empleado_id;

        $this->compartidosPendientes = GastoCompartido::where('empleado_receptor_id', $empleadoId)
            ->where('estatus', 'pendiente')
            ->with(['gastoPagador.concepto', 'gastoPagador.solicitud.empleado'])
            ->get()
            ->map(fn ($c) => [
                'id'              => $c->id,
                'pagador'         => $c->gastoPagador->solicitud->empleado->nombre_completo ?? '—',
                'concepto'        => $c->gastoPagador->concepto->nombre ?? '—',
                'fecha'           => $c->gastoPagador->fecha_gasto?->format('d/m/Y'),
                'monto_compartido'=> (float) $c->monto_compartido,
                'folio_solicitud' => $c->gastoPagador->solicitud->folio ?? '—',
            ])
            ->toArray();
    }

    public function vincularCompartido(int $compartidoId, int $gastoReceptorId, GastoCompartidoService $service): void
    {
        $compartido    = GastoCompartido::findOrFail($compartidoId);
        $gastoReceptor = Gasto::findOrFail($gastoReceptorId);

        try {
            $service->vincularReceptor($compartido, $gastoReceptor, auth()->user());

            $this->compartidoParaVincular = null;
            $this->sincronizarGastos();
            $this->cargarCompartidosPendientes();

            Flux::toast(variant: 'success', text: 'Gasto compartido vinculado. Tu concepto queda comprobado por referencia.');
        } catch (\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    public function toggleExtensionTarjeta(int $detalleId): void
    {
        if (!$this->tieneTarjetaCorporativa) {
            return;
        }

        $detalle  = SolicitudDetalle::where('id', $detalleId)
            ->where('solicitud_id', $this->solicitud->id)
            ->firstOrFail();

        $datoDetalle = collect($this->detalles)->firstWhere('id', $detalleId);
        $excedente   = max(0, $datoDetalle['monto_estimado'] - ($datoDetalle['limite_politica'] ?? 0));

        if ($datoDetalle['tipo_limite_politica'] === 'Diario'
                && $this->solicitud?->fecha_inicio
                && $this->solicitud?->fecha_fin) {

            $duracion = $this->solicitud->fecha_inicio->diffInDays($this->solicitud->fecha_fin) + 1;
            $total = $datoDetalle['limite_politica'] * $duracion;
            $excedente = $datoDetalle['monto_estimado'] - $total;
        }

        if ($detalle->requiere_extension_tarjeta) {
            $detalle->update([
                'requiere_extension_tarjeta' => false,
                'monto_extension_tarjeta'    => null,
            ]);
        } else {
            $detalle->update([
                'requiere_extension_tarjeta' => true,
                'monto_extension_tarjeta'    => $excedente,
            ]);
        }

        $this->solicitud = $this->solicitud->fresh(['detalles.concepto']);
        $this->sincronizarDetalles();
        $this->calcularKpis();
    }

    public function guardarMontoExtension(int $detalleId, float $monto): void
    {
        $detalle = SolicitudDetalle::where('id', $detalleId)
            ->where('solicitud_id', $this->solicitud->id)
            ->firstOrFail();

        $datoDetalle = collect($this->detalles)->firstWhere('id', $detalleId);
        $montoSolicitud = $datoDetalle['monto_estimado'] - $monto;

        if ($montoSolicitud < 0) {
            $this->addError("extension_{$detalleId}", 'El monto de tarjeta no puede superar el total del concepto.');
            return;
        }

        $detalle->update(['monto_extension_tarjeta' => $monto]);

        $this->solicitud = $this->solicitud->fresh(['detalles.concepto']);
        $this->sincronizarDetalles();
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
                    'id'                         => $d->id,
                    'concepto_id'                => $d->concepto_id,
                    'concepto_nombre'            => $d->concepto->nombre ?? '—',
                    'monto_estimado'             => $monto,
                    'limite_politica'            => $politica ? (float) $politica->monto_max : null,
                    'tipo_limite_politica'       => $politica ? $politica->tipo_limite : '',
                    'permite_excepcion'           => $politica?->permite_excepcion ?? false,
                    'comprobante_requerido'      => $comprobanteRequerido,
                    'semaforo'                   => $semaforo,
                    'requiere_extension_tarjeta' => (bool) $d->requiere_extension_tarjeta,
                    'monto_extension_tarjeta'    => $d->monto_extension_tarjeta
                        ? (float) $d->monto_extension_tarjeta
                        : null,
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

        $ctExtension = ComprobacionTarjeta::where('solicitud_id', $this->solicitud->id)
            ->where('es_extension', true)
            ->with('gastos')
            ->first();

        $gastosCtPorConcepto = $ctExtension
            ? $ctExtension->gastos->keyBy('concepto_id')
            : collect();

        $this->gastos = $gastosDb->map(function ($g) use ($politicas, $ctExtension, $gastosCtPorConcepto) {
            $politica     = $politicas->get($g->concepto_id);
            $monto        = (float) $g->monto;
            $totalComp    = $g->comprobantes->sum('monto');
            $gastoExtensionCT = $gastosCtPorConcepto->get($g->concepto_id);

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
                    'subtotal'         => $c->subtotal ? (float) $c->subtotal : null,
                    'iva'              => $c->iva  ? (float) $c->iva  : null,
                    'ieps'             => $c->ieps ? (float) $c->ieps : null,
                    'ish'              => $c->ish  ? (float) $c->ish  : null,
                    'uuid'             => $c->uuid,
                    'sat_status'       => $c->sat_status,
                    'validacion_manual'=> $c->validacion_manual,
                    'archivo'           => $c->archivo,
                    'archivo_pdf'       => $c->archivo_pdf,
                ])->toArray(),
                'compartido' => $g->compartidoComo ? [
                    'id'              => $g->compartidoComo->id,
                    'tipo'            => $g->compartidoComo->tipo,
                    'receptor'        => $g->compartidoComo->empleadoReceptor->nombre_completo ?? $g->compartidoComo->cliente_descripcion,
                    'monto_compartido'=> (float) $g->compartidoComo->monto_compartido,
                    'estatus'         => $g->compartidoComo->estatus,
                ] : null,
                'extension_tarjeta' => $gastoExtensionCT ? [
                    'ct_folio'     => $ctExtension->folio,
                    'ct_id'        => $ctExtension->id,
                    'monto'        => (float) $gastoExtensionCT->monto,
                    'estatus_ct'   => $ctExtension->estatus,
                    'comprobado'   => $gastoExtensionCT->estatus === 'comprobado',
                ] : null,
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
            $this->cargarCompartidosPendientes();
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

    private function obtenerConfiguracion(Gasto $gasto): ConfiguracionEmpresa
    {
        $empresa = $gasto->empleado?->empresa;
        return ConfiguracionEmpresa::obtenerPorEmpresa($empresa);
    }

    public function render()
    {
        return view('livewire.solicitudes.detail.show');
    }
}
