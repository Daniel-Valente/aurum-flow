<?php

namespace App\Livewire\Solicitudes;

use App\Models\Solicitud;
use App\Services\Proyecto\ProyectoService;
use App\Services\Solicitudes\SolicitudService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class FormModal extends Component
{
    public ?int $editingId = null;

    public ?int   $proyecto_id  = null;
    public string $fecha_inicio = '';
    public string $fecha_fin    = '';
    public string $motivo       = '';

    public array $proyectos = [];

    public function mount(ProyectoService $service): void
    {
        $this->proyectos = $service->list();
    }

    #[On('openSolicitudForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $solicitud = Solicitud::findOrFail($id);

            // ✅ Solo editable en Borrador
            if ($solicitud->estatus !== 'Borrador') {
                $this->dispatch('solicitudError', message: 'Solo se pueden editar solicitudes en borrador.');
                return;
            }

            $this->editingId    = $id;
            $this->proyecto_id  = $solicitud->proyecto_id;
            // ✅ format() no forma() + null-safe por si las fechas son null
            $this->fecha_inicio = $solicitud->fecha_inicio?->format('Y-m-d') ?? '';
            $this->fecha_fin    = $solicitud->fecha_fin?->format('Y-m-d')    ?? '';
            $this->motivo       = $solicitud->motivo ?? '';
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('solicitud-form')->show();
    }

    public function save(SolicitudService $service): void
    {
        $this->validate([
            'proyecto_id'  => 'required|exists:proyectos,id',
            'motivo'       => 'required|string|max:500',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ], [
            'proyecto_id.required'        => 'El proyecto es obligatorio.',
            'proyecto_id.exists'          => 'El proyecto seleccionado no es válido.',
            'motivo.required'             => 'El motivo es obligatorio.',
            'motivo.max'                  => 'El motivo no puede exceder 500 caracteres.',
            'fecha_inicio.required'       => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date'           => 'La fecha de inicio no es válida.',
            'fecha_fin.required'          => 'La fecha de finalización es obligatoria.',
            'fecha_fin.date'              => 'La fecha de finalización no es válida.',
            'fecha_fin.after_or_equal'    => 'La fecha fin debe ser igual o posterior a la fecha inicio.',
        ]);

        // ✅ Sin 'folio' ni 'monto_total' — el service los maneja
        $data = [
            'proyecto_id'  => $this->proyecto_id,
            'motivo'       => $this->motivo,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin'    => $this->fecha_fin,
        ];

        try {
            if ($this->editingId) {
                $solicitud = $service->update(Solicitud::findOrFail($this->editingId), $data);
                $msg = 'Solicitud actualizada correctamente.';
                $isNew = false;
            } else {
                $solicitud = $service->create($data, auth()->user());
                $msg = 'Solicitud creada correctamente.';
                $isNew = true;
            }

            $this->modal('solicitud-form')->close();
            $this->resetForm();
            $this->dispatch('solicitudSaved', message: $msg, id: $solicitud->id, isNew: $isNew);

        } catch (\Exception $e) {
            $this->modal('solicitud-form')->close();
            $this->resetForm();
            $this->dispatch('solicitudError', message: $e->getMessage() ?: 'Error al guardar la solicitud.');
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'proyecto_id', 'motivo', 'fecha_inicio', 'fecha_fin']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.solicitudes.form-modal');
    }
}
