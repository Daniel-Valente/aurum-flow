<?php

namespace App\Livewire\Presupuestos;

use App\Models\Presupuesto;
use App\Services\Area\AreaService;
use App\Services\Empleado\EmpleadoService;
use App\Services\Empresa\EmpresaService;
use App\Services\Presupuesto\PresupuestoService;
use App\Services\Proyecto\ProyectoService;
use Livewire\Attributes\On;
use Livewire\Component;

class FormModal extends Component
{
    public ?int $editingId = null;

    public array $empresas = [];
    public array $areas = [];
    public array $empleados = [];
    public array $proyectos = [];

    public string $codigo = '';
    public string $nombre = '';
    public string $descripcion = '';
    public string $tipo = 'empleado';
    public ?int $empresa_id = null;
    public ?int $area_id = null;
    public ?int $empleado_id = null;
    public ?int $proyecto_id = null;
    public string $monto_total = '';
    public string $periodo = 'mensual';
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public bool $renovable = false;
    public string $frecuencia_renovacion = 'mensual';
    public string $alerta_porcentaje = '80.00';
    public string $critico_porcentaje = '95.00';
    public string $notas = '';

    #[On('openPresupuestoForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $presupuesto = Presupuesto::findOrFail($id);

            $this->editingId = $presupuesto->id;
            $this->codigo = $presupuesto->codigo;
            $this->nombre = $presupuesto->nombre;
            $this->descripcion = $presupuesto->descripcion ?? '';
            $this->tipo = $presupuesto->tipo;
            $this->empresa_id = $presupuesto->empresa_id;
            $this->area_id = $presupuesto->area_id;
            $this->empleado_id = $presupuesto->empleado_id;
            $this->proyecto_id = $presupuesto->proyecto_id;
            $this->monto_total = number_format($presupuesto->monto_total, 2, '.', '');
            $this->periodo = $presupuesto->periodo;
            $this->fecha_inicio = $presupuesto->fecha_inicio->format('Y-m-d');
            $this->fecha_fin = $presupuesto->fecha_fin->format('Y-m-d');
            $this->renovable = $presupuesto->renovable;
            $this->frecuencia_renovacion = $presupuesto->frecuencia_renovacion ?? 'mensual';
            $this->alerta_porcentaje = number_format($presupuesto->alerta_porcentaje, 2, '.', '');
            $this->critico_porcentaje = number_format($presupuesto->critico_porcentaje, 2, '.', '');
            $this->notas = $presupuesto->notas ?? '';
        } else {
            $this->resetForm();

            $this->fecha_inicio = now()->startOfMonth()->format('Y-m-d');
            $this->fecha_fin = now()->endOfMonth()->format('Y-m-d');
        }

        $this->resetValidation();
        $this->modal('presupuesto-form')->show();
    }

    public function updatedTipo(): void
    {
        $this->reset(['empresa_id', 'area_id', 'empleado_id', 'proyecto_id']);
    }

    public function updatedPeriodo(): void
    {
        if (!$this->editingId) {
            $inicio = now()->startOfMonth();

            $fin = match($this->periodo) {
                'diario' => now()->endOfDay(),
                'semanal' => now()->endOfWeek(),
                'quincenal' => now()->addDays(14)->endOfDay(),
                'mensual' => now()->endOfMonth(),
                'trimestral' => now()->addMonths(3)->endOfDay(),
                'semestral' => now()->addMonths(6)->endOfDay(),
                'anual' => now()->endOfYear(),
                default => now()->endOfMonth(),
            };

            $this->fecha_inicio = $inicio->format('Y-m-d');
            $this->fecha_fin = $fin->format('Y-m-d');
        }
    }

    public function save(PresupuestoService $service): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:empresa,area,empleado,proyecto',
            'monto_total' => 'required|numeric|min:0.01',
            'periodo' => 'required|in:diario,semanal,quincenal,mensual,trimestral,semestral,anual,proyecto,evento',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'alerta_porcentaje' => 'required|numeric|min:0|max:100',
            'critico_porcentaje' => 'required|numeric|min:0|max:100|gte:alerta_porcentaje',
        ];

        match($this->tipo) {
            'empresa' => $rules['empresa_id'] = 'required|exists:empresas,id',
            'area' => $rules['area_id'] = 'required|exists:areas,id',
            'empleado' => $rules['empleado_id'] = 'required|exists:empleados,id',
            'proyecto' => $rules['proyecto_id'] = 'required|exists:proyectos,id',
        };

        if ($this->renovable) {
            $rules['frecuencia_renovacion'] = 'required|in:diario,semanal,quincenal,mensual,trimestral,semestral,anual';
        }

        $this->validate($rules, [
            'nombre.required' => 'El nombre es obligatorio.',
            'tipo.required' => 'El tipo es obligatorio.',
            'monto_total.required' => 'El monto es obligatorio.',
            'monto_total.min' => 'El monto debe ser mayor a 0.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'empresa_id.required' => 'La empresa es obligatoria para presupuestos de tipo empresa.',
            'area_id.required' => 'El área es obligatoria para presupuestos de tipo área.',
            'empleado_id.required' => 'El empleado es obligatorio para presupuestos de tipo empleado.',
            'proyecto_id.required' => 'El proyecto es obligatorio para presupuestos de tipo proyecto.',
            'critico_porcentaje.gte' => 'El porcentaje crítico debe ser mayor o igual al de alerta.',
        ]);

        $data = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
            'tipo' => $this->tipo,
            'empresa_id' => $this->empresa_id,
            'area_id' => $this->area_id,
            'empleado_id' => $this->empleado_id,
            'proyecto_id' => $this->proyecto_id,
            'monto_total' => (float) $this->monto_total,
            'periodo' => $this->periodo,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'renovable' => $this->renovable,
            'frecuencia_renovacion' => $this->renovable ? $this->frecuencia_renovacion : null,
            'alerta_porcentaje' => (float) $this->alerta_porcentaje,
            'critico_porcentaje' => (float) $this->critico_porcentaje,
            'notas' => $this->notas ?: null,
        ];

        try {
            if ($this->editingId) {
                $presupuesto = Presupuesto::findOrFail($this->editingId);
                $service->update($presupuesto, $data, auth()->user());
                $msg = 'Presupuesto actualizado correctamente.';
            } else {
                $service->create($data, auth()->user());
                $msg = 'Presupuesto creado correctamente.';
            }

            $this->modal('presupuesto-form')->close();
            $this->resetForm();
            $this->dispatch('presupuestoSaved', message: $msg);
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function mount(
        AreaService $areaService,
        EmpleadoService $empleadoService,
        ProyectoService $proyectoService,
        EmpresaService $empresaService,
    ): void {
        $this->empresas = $empresaService->list();
        $this->areas = $areaService->list();
        $this->empleados = $empleadoService->list();
        $this->proyectos = $proyectoService->list();
    }

    public function render()
    {
        return view('livewire.presupuestos.form-modal');
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'codigo',
            'nombre',
            'descripcion',
            'tipo',
            'empresa_id',
            'area_id',
            'empleado_id',
            'proyecto_id',
            'monto_total',
            'periodo',
            'fecha_inicio',
            'fecha_fin',
            'renovable',
            'frecuencia_renovacion',
            'alerta_porcentaje',
            'critico_porcentaje',
            'notas',
        ]);

        $this->tipo = 'empleado';
        $this->periodo = 'mensual';
        $this->alerta_porcentaje = '80.00';
        $this->critico_porcentaje = '95.00';

        $this->resetValidation();
    }
}
