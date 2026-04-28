<?php

namespace App\Livewire\Conceptos;

use App\Models\Concepto;
use App\Services\Concepto\ConceptoService;
use App\Services\Empleado\EmpleadoService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class FormModal extends Component
{
    public ?int $editingId = null;

    public string $codigo = '';
    public string $nombre = '';
    public string $categoria = '';
    public string $descripcion = '';
    public string $tipo_aplicacion = '';

    public ?int $orden = null;

    public bool $requiere_factura = false;
    public bool $requiere_comprobante = false;
    public bool $requiere_uuid = false;
    public bool $permite_sin_factura = false;
    public bool $aplica_iva = false;
    public bool $acumulable_dia = false;

    public ?string $tope_referencia = null;

    public ?string $vigencia_desde = null;
    public ?string $vigencia_hasta = null;

    public array $rolesSeleccionados = [];

    public array $roles = [];

    #[On('openConceptoForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $concepto = Concepto::with('roles')->findOrFail($id);

            $this->editingId = $concepto->id;

            $this->codigo = $concepto->codigo;
            $this->nombre = $concepto->nombre;
            $this->categoria = $concepto->categoria;
            $this->descripcion = $concepto->descripcion;
            $this->tipo_aplicacion = $concepto->tipo_aplicacion;

            $this->orden = $concepto->orden;

            $this->requiere_factura = $concepto->requiere_factura;
            $this->requiere_comprobante = $concepto->requiere_comprobante;
            $this->requiere_uuid = $concepto->requiere_uuid;
            $this->permite_sin_factura = $concepto->permite_sin_factura;
            $this->aplica_iva = $concepto->aplica_iva;
            $this->acumulable_dia = $concepto->acumulable_dia;

            $this->tope_referencia = $concepto->tope_referencia;

            $this->vigencia_desde = $concepto->vigencia_desde?->format('Y-m-d');
            $this->vigencia_hasta = $concepto->vigencia_hasta?->format('Y-m-d');

            $this->setRolesSeleccionados(
                $concepto->roles->pluck('name')->toArray()
            );
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('concepto-form')->show();
    }

    public function setRolesSeleccionados(array $ids): void
    {
        $this->rolesSeleccionados = array_map('strval', $ids);
    }

    public function updatedRequiereUuid(bool $value): void
    {
        if ($value) {
            $this->requiere_factura = true;
            $this->permite_sin_factura = false;
        }
    }

    public function updatedRequiereFactura(bool $value): void
    {
        if ($value) {
            $this->permite_sin_factura = false;
        }
        else {
            $this->requiere_uuid = false;
        }
    }

    public function updatedPermiteSinFactura(bool $value): void
    {
        if ($value) {
            $this->requiere_factura = false;
            $this->requiere_uuid    = false;
        }
    }

    public function save(ConceptoService $service): void
    {
        if ($this->requiere_factura && $this->permite_sin_factura) {
            $this->addError('permite_sin_factura', 'No puede requerir factura y permitir sin factura al mismo tiempo.');
            return;
        }

        if ($this->requiere_uuid && !$this->requiere_factura) {
            $this->addError('requiere_uuid', 'El UUID de factura requiere que se exija factura.');
            return;
        }

        $this->validate([
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('conceptos', 'codigo')->ignore($this->editingId)
            ],
            'nombre' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',

            'tipo_aplicacion' => 'required|string|in:Diario,Evento,Viaje',

            'orden' => 'nullable|integer|min:0',

            'requiere_factura' => 'boolean',
            'requiere_comprobante' => 'boolean',
            'requiere_uuid' => 'boolean',
            'permite_sin_factura' => 'boolean',
            'aplica_iva' => 'boolean',
            'acumulable_dia' => 'boolean',

            'tope_referencia' => 'nullable|numeric|min:0',

            'vigencia_desde' => 'nullable|date',
            'vigencia_hasta' => 'nullable|date|after_or_equal:vigencia_desde',

            'rolesSeleccionados'   => 'array',
            'rolesSeleccionados.*' => 'exists:roles,name',
        ], messages: [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código no puede exceder 20 caracteres.',
            'codigo.unique' => 'Este código ya está en uso.',

            'nombre.required' => 'El nombre es obligatorio.',

            'tipo_aplicacion.required' => 'El tipo de aplicación es obligatorio.',
            'tipo_aplicacion.in' => 'El tipo de aplicación debe ser Diario, Evento o Viaje.',

            'orden.integer' => 'El orden debe ser un número entero.',
            'orden.min' => 'El orden no puede ser negativo.',

            'requiere_factura.boolean' => 'El campo requiere factura debe ser verdadero o falso.',
            'requiere_comprobante.boolean' => 'El campo requiere comprobante debe ser verdadero o falso.',
            'requiere_uuid.boolean' => 'El campo requiere UUID debe ser verdadero o falso.',
            'permite_sin_factura.boolean' => 'El campo permite sin factura debe ser verdadero o falso.',
            'aplica_iva.boolean' => 'El campo aplica IVA debe ser verdadero o falso.',
            'acumulable_dia.boolean' => 'El campo acumulable por día debe ser verdadero o falso.',

            'tope_referencia.numeric' => 'El tope de referencia debe ser un número.',
            'tope_referencia.min' => 'El tope de referencia no puede ser negativo.',

            'vigencia_desde.date' => 'La fecha de vigencia desde no es válida.',
            'vigencia_hasta.date' => 'La fecha de vigencia hasta no es válida.',
            'vigencia_hasta.after_or_equal' => 'La vigencia hasta debe ser igual o posterior a la vigencia desde.',
        ]);

        $data = [
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'categoria' => $this->categoria,
            'descripcion' => $this->descripcion,
            'tipo_aplicacion' => $this->tipo_aplicacion,
            'orden' => $this->orden ?? 0,
            'requiere_factura' => $this->requiere_factura,
            'requiere_comprobante' => $this->requiere_comprobante,
            'requiere_uuid' => $this->requiere_uuid,
            'permite_sin_factura' => $this->permite_sin_factura,
            'aplica_iva' => $this->aplica_iva,
            'acumulable_dia' => $this->acumulable_dia,
            'tope_referencia' => $this->tope_referencia,
            'vigencia_desde' => $this->vigencia_desde,
            'vigencia_hasta' => $this->vigencia_hasta,
            'roles' => $this->rolesSeleccionados,
            'estatus' => true
        ];

        if ($this->editingId) {
            $service->update(Concepto::findOrFail($this->editingId), $data);
            $msg = 'Concepto actualiazdo correctamente.';
        } else {
            $service->create($data);
            $msg = 'Concepto creado correctamente.';
        }

        $this->modal('concepto-form')->close();
        $this->resetForm();

        $this->dispatch('conceptoSaved', message: $msg);
    }

    public function resetForm(): void
    {
        $this->reset(['codigo', 'nombre', 'descripcion', 'categoria', 'tipo_aplicacion', 'orden', 'requiere_factura', 'requiere_comprobante', 'requiere_uuid', 'permite_sin_factura', 'aplica_iva', 'acumulable_dia', 'tope_referencia', 'vigencia_desde', 'vigencia_hasta']);
        $this->resetValidation();
    }

    public function mount(EmpleadoService $empleadoService): void
    {
        $this->roles = $empleadoService->roles();
    }

    public    function render()
    {
        return view('livewire.conceptos.form-modal');
    }
}
