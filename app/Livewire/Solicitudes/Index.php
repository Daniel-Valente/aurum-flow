<?php

namespace App\Livewire\Solicitudes;

use App\Models\Solicitud;
use App\Services\Solicitudes\SolicitudService;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $estatus      = '';
    public string $cumplimiento = '';  // ok | con_excepcion | rechazado | sin_captura

    public ?int   $deletingId     = null;
    public string $deletingNombre = '';

    public ?int  $createdId    = null;
    public string $createdFolio = '';

    // ── Resetear página al cambiar filtros ──────────────────────────────────
    public function updatedSearch():       void { $this->resetPage(); }
    public function updatedEstatus():      void { $this->resetPage(); }
    public function updatedCumplimiento(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['search', 'estatus', 'cumplimiento']);
        $this->resetPage();
    }

    // ── Acciones de navegación ──────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->dispatch('openSolicitudForm');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('openSolicitudForm', id: $id);
    }

    public function show(int $id): void
    {
        $this->redirectRoute('solicitudes.show', $id);
    }

    public function openDetail(int $id): void
    {
        $this->dispatch('openSolicitudDetail', id: $id);
    }

    // ── Cancelar solicitud (toggle) ─────────────────────────────────────────

    public function openDelete(int $id): void
    {
        $solicitud = Solicitud::findOrFail($id);

        // Solo Borrador y Pendiente pueden cancelarse
        if (!in_array($solicitud->estatus, ['Borrador', 'Pendiente'], true)) {
            Flux::toast(variant: 'warning', text: 'Esta solicitud no puede cancelarse en su estado actual.');
            return;
        }

        $this->deletingId     = $id;
        $this->deletingNombre = $solicitud->folio;

        $this->modal('solicitud-delete')->show();
    }

    public function delete(SolicitudService $service): void
    {
        if (!$this->deletingId) {
            return;
        }

        try {
            $solicitud = Solicitud::findOrFail($this->deletingId);
            $service->cancelar($solicitud, auth()->user());

            $this->modal('solicitud-delete')->close();
            $this->reset(['deletingId', 'deletingNombre']);

            Flux::toast(variant: 'success', text: 'Solicitud cancelada correctamente.');
        } catch (\Exception $e) {
            $this->modal('solicitud-delete')->close();
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    // ── Eventos desde FormModal ─────────────────────────────────────────────

    #[On('solicitudSaved')]
    public function onSolicitudSaved(string $message, int $id, bool $isNew = false): void
    {
        Flux::toast(variant: 'success', text: $message);

        if ($isNew) {
            $solicitud = Solicitud::select('id', 'folio')->findOrFail($id);
            $this->createdId    = $solicitud->id;
            $this->createdFolio = $solicitud->folio;

            $this->modal('solicitud-creada')->show();
        }
    }

    public function stayHere(): void
    {
        $this->reset(['createdId', 'createdFolio']);
        $this->modal('solicitud-creada')->close();
    }

    public function goToDetail(): void
    {
        $id = $this->createdId;
        $this->reset(['createdId', 'createdFolio']);
        $this->modal('solicitud-creada')->close();
        $this->redirectRoute('solicitudes.show', $id);
    }

    #[On('solicitudError')]
    public function onSolicitudError(string $message): void
    {
        Flux::toast(variant: 'danger', text: $message);
    }

    // ── Render ──────────────────────────────────────────────────────────────

    public function render(SolicitudService $service)
    {
        $solicitudes = $service->paginate(
            user:        auth()->user(),
            search:      $this->search,
            estatus:     $this->estatus,
            cumplimiento: $this->cumplimiento,
        );

        // KPIs — agregados desde la colección ya paginada (sin query extra)
        // Para mayor precisión se podrían calcular en el service con subqueries
        $kpis = [
            'total'        => $solicitudes->total(),
            'presupuesto'  => $solicitudes->sum('monto_total'),
            'comprobado'   => $solicitudes->sum('monto_comprobado'),
            'excepciones'  => $solicitudes->sum('excepciones_n1') + $solicitudes->sum('excepciones_n2'),
        ];

        return view('livewire.solicitudes.index', compact('solicitudes', 'kpis'));
    }
}
