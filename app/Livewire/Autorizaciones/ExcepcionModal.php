<?php

namespace App\Livewire\Autorizaciones;

use App\Models\GastoExcepcion;
use App\Services\Gasto\GastoExcepcionService;
use Livewire\Attributes\On;
use Livewire\Component;

class ExcepcionModal extends Component
{
    public ?GastoExcepcion $excepcion = null;
    public bool   $confirmandoRechazo = false;
    public string $comentario         = '';

    #[On('openExcepcionDetail')]
    public function open(int $id): void
    {
        $this->excepcion = GastoExcepcion::with([
            'gasto.concepto',
            'gasto.solicitud.empleado',
            'gasto.solicitud.proyecto',
            'aprobador',
        ])->findOrFail($id);

        $this->confirmandoRechazo = false;
        $this->comentario         = '';
        $this->resetValidation();

        $this->modal('excepcion-detail')->show();
    }

    public function close(): void
    {
        $this->excepcion = null;
        $this->modal('excepcion-detail')->close();
    }

    public function iniciarRechazo(): void
    {
        $this->confirmandoRechazo = true;
        $this->comentario = '';
        $this->resetValidation();
    }

    public function cancelarRechazo(): void
    {
        $this->confirmandoRechazo = false;
    }

    public function aprobar(GastoExcepcionService $service): void
    {
        try {
            $service->resolver($this->excepcion, auth()->user(), 'aprobado');

            $this->modal('excepcion-detail')->close();
            $this->dispatch('excepcionResuelta', message: 'Excepción aprobada correctamente.');
        } catch (\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    public function rechazar(GastoExcepcionService $service): void
    {
        $this->validate([
            'comentario' => 'required|string|min:10|max:500',
        ], [
            'comentario.required' => 'El motivo es obligatorio.',
            'comentario.min'      => 'Mínimo 10 caracteres.',
        ]);

        try {
            $service->resolver($this->excepcion, auth()->user(), 'rechazado', $this->comentario);

            $this->confirmandoRechazo = false;
            $this->modal('excepcion-detail')->close();
            $this->dispatch('excepcionResuelta', message: 'Excepción rechazada.');
        } catch (\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.autorizaciones.excepcion-modal');
    }
}
