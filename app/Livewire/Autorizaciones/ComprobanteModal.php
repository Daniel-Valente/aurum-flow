<?php

namespace App\Livewire\Autorizaciones;

use App\Models\GastoComprobante;
use App\Services\Gasto\ComprobanteValidacionService;
use Livewire\Attributes\On;
use Livewire\Component;
use Storage;

class ComprobanteModal extends Component
{
    public bool $open = false;

    public ?GastoComprobante $comprobante = null;
    public string $comentario = '';
    public string $accion     = ''; // 'aprobado' | 'rechazado'

    #[On('openComprobanteDetail')]
    public function abrir(int $id): void
    {
        $this->comprobante = GastoComprobante::with([
            'gasto.solicitud.empleado',
            'gasto.solicitud.proyecto',
            'gasto.concepto',
        ])->findOrFail($id);

        $this->reset(['comentario', 'accion']);
        $this->resetValidation();
        $this->open = true;
    }

    public function cerrar(): void
    {
        $this->open        = false;
        $this->comprobante = null;
        $this->reset(['comentario', 'accion']);
        $this->resetValidation();
    }

    public function resolver(string $accion, ComprobanteValidacionService $service): void
    {
        $this->accion = $accion;

        $this->validate([
            'comentario' => $accion === 'rechazado'
                ? 'required|string|min:10|max:500'
                : 'nullable|string|max:500',
        ], [
            'comentario.required' => 'El motivo de rechazo es obligatorio.',
            'comentario.min'      => 'Mínimo 10 caracteres.',
        ]);

        try {
            $service->resolver(
                comprobante: $this->comprobante,
                user: auth()->user(),
                accion: $accion,
                comentario: $this->comentario ?: null,
            );

            $label = $accion === 'aprobado' ? 'aprobado' : 'rechazado';
            $this->cerrar();
            $this->dispatch('comprobanteValidado', message: "Comprobante {$label} correctamente.");

        } catch (\Exception $e) {
            $this->dispatch('autorizacionError', message: $e->getMessage());
        }
    }

    public function descargar(): mixed
    {
        if (!$this->comprobante) {
            $this->dispatch('autorizacionError', message: 'Comprobante no encontrado.');
            return null;
        }

        $path = $this->comprobante->archivo;

        if (!Storage::disk('private')->exists($path)) {
            $this->dispatch('autorizacionError', message: 'Archivo no encontrado en el servidor.');
            return null;
        }

        return response()->download(
            Storage::disk('private')->path($path),
            basename($path)
        );
    }

    public function descargarPdf(): mixed
    {
        if (!$this->comprobante?->archivo_pdf) {
            $this->dispatch('autorizacionError', message: 'No hay PDF adjunto.');
            return null;
        }

        $path = $this->comprobante->archivo_pdf;

        if (!Storage::disk('private')->exists($path)) {
            $this->dispatch('autorizacionError', message: 'PDF no encontrado.');
            return null;
        }

        return response()->download(
            Storage::disk('private')->path($path),
            basename($path)
        );
    }

    public function render()
    {
        return view('livewire.autorizaciones.comprobante-modal');
    }
}
