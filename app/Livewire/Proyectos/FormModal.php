<?php

namespace App\Livewire\Proyectos;

use App\Models\Empleado;
use App\Models\Proyecto;
use App\Services\CentroCosto\CentroCostoService;
use App\Services\Empleado\EmpleadoService;
use App\Services\Proyecto\ProyectoService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class FormModal extends Component
{
    public ?int $editingId = null;

    public string $codigo = '';
    public string $nombre = '';
    public string $cliente = '';
    public string $tipo = '';
    public string $descripcion = '';

    public string $prioridad = '';
    public string $estado_operativo = '';

    public ?int $centro_costo_id = null;
    public ?int $responsable_id = null;

    public ?string $presupuesto_total = null;
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;

    public ?string $ciudad = null;
    public ?string $estado = null;
    public ?string $region = null;
    public ?string $pais = null;

    public array $empleados = [];
    public array $centrosCostos = [];

    #[On('openProyectoForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $proyecto = Proyecto::with(['centroCosto', 'responsable'])->findOrFail($id);

            $this->editingId = $proyecto->id;

            $this->codigo = $proyecto->codigo;
            $this->nombre = $proyecto->nombre;
            $this->cliente = $proyecto->cliente;
            $this->tipo = $proyecto->tipo;
            $this->descripcion = $proyecto->descripcion;

            $this->prioridad = $proyecto->prioridad;
            $this->estado_operativo = $proyecto->estado_operativo;

            $this->centro_costo_id = $proyecto->centro_costo_id;
            $this->responsable_id = $proyecto->responsable_id;

            $this->presupuesto_total = $proyecto->presupuesto_total;
            $this->fecha_inicio = $proyecto->fecha_inicio?->format('Y-m-d');
            $this->fecha_fin = $proyecto->fecha_fin?->format('Y-m-d');

            $this->ciudad = $proyecto->ciudad;
            $this->estado = $proyecto->estado;
            $this->region = $proyecto->region;
            $this->pais = $proyecto->pais;
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('proyecto-form')->show();
    }

    public function updatedCentroCostoId($value)
    {
        if (empty($value)) {
            $this->empleados = Empleado::where('estatus', 1)->get()->toArray();
        } else {
            $this->empleados = Empleado::where('centro_costo_id', $value)
                ->where('estatus', 1)
                ->get()
                ->toArray();
        }

        if ($this->responsable_id) {
            $empleado = Empleado::find($this->responsable_id);
            if ($empleado?->centro_costo_id != $value) {
                $this->responsable_id = null;
            }
        }
    }

    public function updatedResponsableId($value)
    {
        if (empty($value)) {
            $this->centro_costo_id = null;

            $this->empleados = Empleado::where('estatus', 1)
                ->get()
                ->toArray();

            return;
        }

        $empleado = Empleado::find($value);

        if ($empleado) {
            $this->centro_costo_id = $empleado->centro_costo_id;

            $this->empleados = Empleado::where(
                    'centro_costo_id',
                    $empleado->centro_costo_id
                )
                ->where('estatus', 1)
                ->get()
                ->toArray();
        }
    }

    public function save(ProyectoService $service): void
    {
        $this->validate([
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('proyectos', 'codigo')->ignore($this->editingId)
            ],

            'tipo' => 'required|in:Proyecto,Ruta,Zona',

            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',

            'cliente' => 'nullable|string|max:255',

            'responsable_id' => 'nullable|exists:empleados,id',
            'centro_costo_id' => 'required|exists:centros_costos,id',

            'prioridad' => 'required|in:Baja,Media,Alta',
            'estado_operativo' => 'required|in:Draft,Activo,Cerrado',

            'presupuesto_total' => 'nullable|numeric|min:0',

            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',

            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'pais' => 'nullable|string|max:100',

        ], messages: [

            'codigo.required' => 'El código es obligatorio.',
            'codigo.string' => 'El código debe ser texto.',
            'codigo.max' => 'El código no puede exceder 20 caracteres.',
            'codigo.unique' => 'Este código ya está en uso.',

            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',

            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',

            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede exceder 255 caracteres.',

            'cliente.string' => 'El cliente debe ser texto.',
            'cliente.max' => 'El cliente no puede exceder 255 caracteres.',

            'responsable_id.exists' => 'El responsable seleccionado no es válido.',

            'centro_costo_id.required' => 'El centro de costos es obligatorio.',
            'centro_costo_id.exists' => 'El centro de costos seleccionado no es válido.',

            'prioridad.required' => 'La prioridad es obligatoria.',
            'prioridad.in' => 'La prioridad seleccionada no es válida.',

            'estado_operativo.required' => 'El estado operativo es obligatorio.',
            'estado_operativo.in' => 'El estado operativo seleccionado no es válido.',

            'presupuesto_total.numeric' => 'El presupuesto total debe ser un número.',
            'presupuesto_total.min' => 'El presupuesto total no puede ser negativo.',

            'fecha_inicio.date' => 'La fecha de inicio no es válida.',
            'fecha_fin.date' => 'La fecha fin no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser igual o posterior a la fecha inicio.',

            'ciudad.string' => 'La ciudad debe ser texto.',
            'ciudad.max' => 'La ciudad no puede exceder 100 caracteres.',

            'estado.string' => 'El estado debe ser texto.',
            'estado.max' => 'El estado no puede exceder 100 caracteres.',

            'region.string' => 'La región debe ser texto.',
            'region.max' => 'La región no puede exceder 100 caracteres.',

            'pais.string' => 'El país debe ser texto.',
            'pais.max' => 'El país no puede exceder 100 caracteres.',
        ]);

        $data = [
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'cliente' => $this->cliente,
            'tipo' => $this->tipo,
            'descripcion' => $this->descripcion,
            'prioridad' => $this->prioridad,
            'estado_operativo' => $this->estado_operativo,
            'centro_costo_id' => $this->centro_costo_id,
            'responsable_id' => $this->responsable_id,
            'presupuesto_total' => $this->presupuesto_total,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'ciudad' => $this->ciudad,
            'estado' => $this->estado,
            'region' => $this->region,
            'pais' => $this->pais,
            'estatus' => true
        ];

        if ($this->editingId) {
            $service->update(Proyecto::findOrFail($this->editingId), $data);
            $msg = 'Proyecto actualizado correctamente.';
        } else {
            $service->create($data);
            $msg = 'Proyecto creado correctamente.';
        }

        $this->modal('proyecto-form')->close();
        $this->resetForm();

        $this->dispatch('proyectoSaved', message: $msg);
    }

    public function resetForm(): void
    {
        $this->reset(['codigo', 'nombre', 'cliente', 'tipo', 'descripcion', 'prioridad', 'estado_operativo', 'centro_costo_id', 'responsable_id', 'presupuesto_total', 'fecha_inicio', 'fecha_fin', 'ciudad', 'estado', 'region', 'pais']);
        $this->resetValidation();
    }

    public function mount(EmpleadoService $empleadoService, CentroCostoService $centroCostoService): void
    {
        $this->empleados = $empleadoService->list();
        $this->centrosCostos = $centroCostoService->list();
    }

    public function render()
    {
        return view('livewire.proyectos.form-modal');
    }
}
