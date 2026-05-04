<?php

namespace App\Livewire\Politicas;

use App\Models\Concepto;
use App\Models\PoliticaGasto;
use App\Services\Concepto\ConceptoService;
use App\Services\Empleado\EmpleadoService;
use App\Services\Gasto\PoliticaGastoService;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class FormModal extends Component
{
    public ?int $editingId = null;

    // — Identificadores —
    public ?int $roleId      = null;
    public ?int $concepto_id = null;

    // — Monto principal —
    public string $monto_max   = '';
    public string $tipo_limite = '';

    // — Tramos documentales —
    // null = ese tramo no aplica para la política
    public ?string $monto_libre       = null;
    public ?string $monto_comprobante = null;
    public ?string $monto_factura     = null;

    // — Flags de comportamiento —
    public bool $valida_sat        = false;
    public bool $acumulable_dia    = true;
    public bool $permite_excepcion = false;

    // — Vigencia —
    public ?string $vigencia_desde = null;
    public ?string $vigencia_hasta = null;

    // — Sólo en edición —
    public ?string $motivo = null;

    // — Datos para selects —
    public array $roles     = [];
    public array $conceptos = [];

    // -------------------------------------------------------------------------
    // Ciclo de vida
    // -------------------------------------------------------------------------

    public function mount(EmpleadoService $empleadoService): void
    {
        $this->roles = $empleadoService->roles();
    }

    // -------------------------------------------------------------------------
    // Apertura del modal
    // -------------------------------------------------------------------------

    #[On('openPoliticaForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $politica = PoliticaGasto::with(['role', 'concepto'])->findOrFail($id);

            $this->editingId = $id;
            $this->roleId    = $politica->role_id;
            $this->concepto_id = $politica->concepto_id;

            $this->monto_max   = (string) $politica->monto_max;
            $this->tipo_limite = $politica->tipo_limite;

            // Tramos documentales
            $this->monto_libre       = $politica->monto_libre       !== null ? (string) $politica->monto_libre       : null;
            $this->monto_comprobante = $politica->monto_comprobante !== null ? (string) $politica->monto_comprobante : null;
            $this->monto_factura     = $politica->monto_factura     !== null ? (string) $politica->monto_factura     : null;

            // Flags
            $this->valida_sat        = $politica->valida_sat;
            $this->acumulable_dia    = $politica->acumulable_dia;
            $this->permite_excepcion = $politica->permite_excepcion;

            // Vigencia
            $this->vigencia_desde = $politica->vigencia_desde?->format('Y-m-d');
            $this->vigencia_hasta = $politica->vigencia_hasta?->format('Y-m-d');

            // Cargar conceptos del rol seleccionado
            $this->conceptos = app(ConceptoService::class)->list((int) $this->roleId);
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('politica-form')->show();
    }

    // -------------------------------------------------------------------------
    // Watchers
    // -------------------------------------------------------------------------

    /**
     * Al cambiar el rol, recarga los conceptos disponibles y limpia la selección.
     */
    public function updatedRoleId($value): void
    {
        $this->conceptos   = empty($value) ? [] : app(ConceptoService::class)->list((int) $value);
        $this->concepto_id = null;
    }

    /**
     * Si se activa validación SAT, la factura es obligatoria desde $0.01.
     * Se auto-completa monto_factura con 0.01 si estaba vacío.
     */
    public function updatedValidaSat(bool $value): void
    {
        if ($value && empty($this->monto_factura)) {
            $this->monto_factura = '0.01';
        }
    }

    // -------------------------------------------------------------------------
    // Validación
    // -------------------------------------------------------------------------

    public function isEditing(): bool
    {
        return $this->editingId !== null;
    }

    private function validationRules(): array
    {
        return [
            'roleId'      => 'required|exists:roles,id',
            'concepto_id' => 'required|exists:conceptos,id',

            'monto_max'   => 'required|numeric|min:0.01',
            'tipo_limite' => 'required|string|in:Diario,Viaje,Evento',

            // Tramos: opcionales, pero deben ser coherentes si se ingresan
            'monto_libre'       => 'nullable|numeric|min:0',
            'monto_comprobante' => 'nullable|numeric|min:0',
            'monto_factura'     => 'nullable|numeric|min:0.01',

            'valida_sat'        => 'boolean',
            'acumulable_dia'    => 'boolean',
            'permite_excepcion' => 'boolean',

            'vigencia_desde' => 'nullable|date',
            'vigencia_hasta' => 'nullable|date|after_or_equal:vigencia_desde',

            'motivo' => $this->isEditing()
                ? 'required|string|min:5'
                : 'nullable|string',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'roleId.required'              => 'El rol es obligatorio.',
            'concepto_id.required'         => 'El concepto es obligatorio.',

            'monto_max.required'           => 'El monto máximo es obligatorio.',
            'monto_max.numeric'            => 'El monto máximo debe ser un número.',
            'monto_max.min'                => 'El monto máximo debe ser mayor a $0.',

            'tipo_limite.required'         => 'El tipo de límite es obligatorio.',
            'tipo_limite.in'               => 'El tipo debe ser Diario, Viaje o Evento.',

            'monto_libre.numeric'          => 'El monto libre debe ser un número.',
            'monto_libre.min'              => 'El monto libre no puede ser negativo.',
            'monto_comprobante.numeric'    => 'El monto comprobante debe ser un número.',
            'monto_comprobante.min'        => 'El monto comprobante no puede ser negativo.',
            'monto_factura.numeric'        => 'El monto factura debe ser un número.',
            'monto_factura.min'            => 'El monto factura debe ser mayor a $0.',

            'vigencia_desde.date'          => 'La vigencia desde no es una fecha válida.',
            'vigencia_hasta.date'          => 'La vigencia hasta no es una fecha válida.',
            'vigencia_hasta.after_or_equal' => 'La vigencia hasta debe ser igual o posterior a la vigencia desde.',

            'motivo.required'              => 'El motivo de cambio es obligatorio al editar.',
            'motivo.min'                   => 'El motivo debe tener al menos 5 caracteres.',
        ];
    }

    /**
     * Validaciones de negocio sobre los tramos (orden y consistencia).
     * Se ejecutan después de la validación de Laravel.
     */
    private function validarTramos(): bool
    {
        $libre       = filled($this->monto_libre)       ? (float) $this->monto_libre       : null;
        $comprobante = filled($this->monto_comprobante) ? (float) $this->monto_comprobante : null;
        $factura     = filled($this->monto_factura)     ? (float) $this->monto_factura     : null;
        $max         = filled($this->monto_max)         ? (float) $this->monto_max         : null;

        // Sin monto_max no hay nada que validar
        if ($max === null || $max <= 0) {
            return true;
        }

        $ok = true;

        // libre < comprobante
        if ($libre !== null && $comprobante !== null && $libre >= $comprobante) {
            $this->addError('monto_libre', 'El monto libre debe ser menor al monto de comprobante.');
            $ok = false;
        }

        // comprobante < factura
        if ($comprobante !== null && $factura !== null && $comprobante >= $factura) {
            $this->addError('monto_comprobante', 'El monto de comprobante debe ser menor al monto de factura.');
            $ok = false;
        }

        // libre < factura (sin comprobante intermedio)
        if ($libre !== null && $factura !== null && $comprobante === null && $libre >= $factura) {
            $this->addError('monto_libre', 'El monto libre debe ser menor al monto de factura.');
            $ok = false;
        }

        // Cada tramo debe ser ESTRICTAMENTE menor al monto_max
        // Mensaje específico por campo para no confundir al usuario
        if ($libre !== null && $libre >= $max) {
            $this->addError('monto_libre', 'El monto libre debe ser menor al máximo (' . number_format($max, 2) . ').');
            $ok = false;
        }

        if ($comprobante !== null && $comprobante >= $max) {
            $this->addError('monto_comprobante', 'El monto de comprobante debe ser menor al máximo (' . number_format($max, 2) . ').');
            $ok = false;
        }

        if ($factura !== null && $factura >= $max) {
            $this->addError('monto_factura', 'El monto de factura debe ser menor al máximo (' . number_format($max, 2) . ').');
            $ok = false;
        }

        // Si valida_sat=true debe tener factura definida
        if ($this->valida_sat && $factura === null) {
            $this->addError('monto_factura', 'Debes definir el monto de factura si activas la validación SAT.');
            $ok = false;
        }

        return $ok;
    }

    // -------------------------------------------------------------------------
    // Guardar
    // -------------------------------------------------------------------------

    public function save(PoliticaGastoService $service): void
    {
        $this->validate($this->validationRules(), $this->validationMessages());

        if (!$this->validarTramos()) {
            return;
        }

        $data = [
            'role_id'           => $this->roleId,
            'concepto_id'       => $this->concepto_id,
            'monto_max'         => $this->monto_max,
            'tipo_limite'       => $this->tipo_limite,

            // Tramos documentales — null si el campo está vacío
            'monto_libre'       => filled($this->monto_libre)       ? $this->monto_libre       : null,
            'monto_comprobante' => filled($this->monto_comprobante) ? $this->monto_comprobante : null,
            'monto_factura'     => filled($this->monto_factura)     ? $this->monto_factura     : null,

            'valida_sat'        => $this->valida_sat,
            'acumulable_dia'    => $this->acumulable_dia,
            'permite_excepcion' => $this->permite_excepcion,

            'vigencia_desde'    => $this->vigencia_desde ?: null,
            'vigencia_hasta'    => $this->vigencia_hasta ?: null,

            'motivo'            => $this->motivo,
            'origen'            => 'manual',
        ];

        try {
            if ($this->isEditing()) {
                $service->update(PoliticaGasto::findOrFail($this->editingId), $data, auth()->user());
                $msg = 'Política actualizada correctamente.';
            } else {
                $service->create($data, auth()->user());
                $msg = 'Política creada correctamente.';
            }

            $this->modal('politica-form')->close();
            $this->resetForm();
            $this->dispatch('politicaSaved', message: $msg);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\PoliticaDuplicadaException $e) {
            $this->addError('concepto_id', $e->getMessage());
        } catch (\Exception $e) {
            $this->modal('politica-form')->close();
            $this->resetForm();
            $this->dispatch('politicaError', message: $e->getMessage() ?: 'Ocurrió un error inesperado.');
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'roleId', 'concepto_id',
            'monto_max', 'tipo_limite',
            'monto_libre', 'monto_comprobante', 'monto_factura',
            'valida_sat', 'acumulable_dia', 'permite_excepcion',
            'vigencia_desde', 'vigencia_hasta',
            'motivo',
        ]);
        $this->acumulable_dia = true; // default
        $this->conceptos      = [];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.politicas.form-modal');
    }
}
