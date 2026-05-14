<?php

namespace App\Livewire\Tarjeta;

use App\Models\ComprobacionTarjeta;
use App\Services\Gasto\ComprobacionTarjetaService;
use App\Services\Proyecto\ProyectoService;
use App\Services\Solicitudes\SolicitudService;
use Livewire\Attributes\On;
use Livewire\Component;

class FormModal extends Component
{
    public ?int $editingId = null;

    public ?int    $proyecto_id  = null;
    public ?int    $solicitud_id = null;
    public string  $fecha_inicio = '';
    public string  $fecha_fin    = '';
    public ?string $descripcion = null;

    public array $proyectos   = [];

    public function mount(ProyectoService $service): void
    {
        $this->proyectos = $service->list();
    }

    #[On('openTarjetaForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $comprobacion = ComprobacionTarjeta::findOrFail($id);

            if ($comprobacion->estatus !== "abierta") {
                $this->modal('tarjeta-form')->close();

                $this->dispatch('gastoError', message: 'Solo se pueden editar comprobación de tarjeta abiertas.');
                return;
            }

            $this->editingId    = $id;
            $this->proyecto_id  = $comprobacion->proyecto_id;

            $this->fecha_inicio = $comprobacion->fecha_inicio?->format('Y-m-d') ?? '';
            $this->fecha_fin    = $comprobacion->fecha_fin?->format('Y-m-d')    ?? '';
            $this->descripcion  = $comprobacion->descripcion ?? '';
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('tarjeta-form')->show();
    }

    public function save(ComprobacionTarjetaService $service): void
    {
        $this->validate([
            'proyecto_id'  => 'nullable|exists:proyectos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'descripcion'  => 'nullable|string|max:300',
        ], [
            'fecha_inicio.required'    => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'       => 'La fecha fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser igual o posterior al inicio.',
        ]);

        try {
            $data = [
                'proyecto_id'  => $this->proyecto_id,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin'    => $this->fecha_fin,
                'descripcion'  => $this->descripcion,
            ];

            if ($this->editingId) {
                $comprobacion = $service->update(ComprobacionTarjeta::findOrFail($this->editingId), $data);
                $msg = "Periodo {$comprobacion->folio} actualizado.";
                $isNew = false;
            }
            else {
                $comprobacion = $service->create($data, auth()->user());
                $msg = "Periodo {$comprobacion->folio} creado.";
                $isNew = true;
            }

            $this->modal('tarjeta-form')->close();
            $this->resetForm();
            $this->dispatch('tarjetaPeriodoCreado', message: $msg, id: $comprobacion->id, isNew: $isNew);
        } catch (\Exception $e) {
            $this->dispatch('tarjetaError', message: $e->getMessage());
        }
    }

    public function resetForm(): void
    {

        $this->reset(['proyecto_id', 'fecha_inicio', 'fecha_fin', 'descripcion']);

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.tarjeta.form-modal');
    }
}
