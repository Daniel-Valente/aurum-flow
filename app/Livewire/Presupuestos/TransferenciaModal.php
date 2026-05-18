<?php

namespace App\Livewire\Presupuestos;

use App\Models\Presupuesto;
use App\Services\Presupuesto\PresupuestoService;
use Livewire\Attributes\On;
use Livewire\Component;

class TransferenciaModal extends Component
{
    public ?int $origenId = null;
    public ?int $destinoId = null;
    public string $monto = '';
    public string $motivo = '';

    public array $presupuestosDisponibles = [];

    #[On('openTransferenciaModal')]
    public function open(int $origenId, PresupuestoService $service): void
    {
        $this->origenId = $origenId;
        $origen = Presupuesto::findOrFail($origenId);

        $this->presupuestosDisponibles = Presupuesto::where('id', '!=', $origenId)
            ->where('estatus', 'activo')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'tipo'])
            ->map(fn($p) => [
                'id' => $p->id,
                'label' => "{$p->codigo} - {$p->nombre} ({$p->tipo})",
            ])
            ->toArray();

        $this->resetForm();
        $this->modal('transferencia-modal')->show();
    }

    public function save(PresupuestoService $service): void
    {
        $this->validate([
            'destinoId' => 'required|exists:presupuestos,id|different:origenId',
            'monto' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|min:10|max:500',
        ], [
            'destinoId.required' => 'Selecciona el presupuesto destino.',
            'destinoId.different' => 'El destino debe ser diferente al origen.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'motivo.required' => 'El motivo es obligatorio.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        try {
            $origen = Presupuesto::findOrFail($this->origenId);
            $destino = Presupuesto::findOrFail($this->destinoId);

            $service->solicitarTransferencia(
                $origen,
                $destino,
                (float) $this->monto,
                $this->motivo,
                auth()->user()
            );

            $this->modal('transferencia-modal')->close();
            $this->resetForm();
            $this->dispatch('transferenciaCreada');
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.presupuestos.transferencia-modal');
    }

    private function resetForm(): void
    {
        $this->reset(['destinoId', 'monto', 'motivo']);
        $this->resetValidation();
    }
}
