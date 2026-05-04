<?php

namespace App\Livewire\Empleados;

use App\Exports\EmpleadosTemplateExport;
use App\Imports\EmpleadosImport;
use Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportModal extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $archivo = null;
    public Collection|array $preview       = [];
    public bool $validado                  = false;

    public int $totalOk     = 0;
    public int $totalError  = 0;

    #[On('openImportEmpleados')]
    public function open(): void
    {
        $this->reset(['archivo', 'preview', 'validado', 'totalOk', 'totalError']);
        $this->preview = [];
        $this->modal('import-empleados')->show();
    }

    public function descargarTemplate()
    {
        return Excel::download(new EmpleadosTemplateExport, 'plantilla_empleados.xlsx');
    }

    public function validar(): void
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'archivo.required' => 'Selecciona un archivo Excel.',
            'archivo.mimes'    => 'Solo se aceptan archivos .xlsx o .xls.',
            'archivo.max'      => 'El archivo no debe superar 5 MB.',
        ]);

        $import = new EmpleadosImport(soloValidar: true);
        Excel::import($import, $this->archivo->getRealPath());

        $this->preview    = $import->preview->toArray();
        $this->totalOk    = collect($this->preview)->where('estado', 'ok')->count();
        $this->totalError = collect($this->preview)->where('estado', 'error')->count();
        $this->validado   = true;
    }

    public function confirmarImport(): void
    {
        if ($this->totalError > 0) {
            Flux::toast(variant: 'danger', text: "Corrige los {$this->totalError} errores antes de importar.");
            return;
        }

        $import = new EmpleadosImport(soloValidar: false);
        Excel::import($import, $this->archivo->getRealPath());

        $this->modal('import-empleados')->close();
        $this->dispatch('empleadosImportados', total: $this->totalOk);

        $this->reset(['archivo', 'preview', 'validado', 'totalOk', 'totalError']);
        $this->preview = [];
    }

    public function resetImport(): void
    {
        $this->reset(['archivo', 'preview', 'validado', 'totalOk', 'totalError']);
        $this->preview = [];
    }

    public function render()
    {
        return view('livewire.empleados.import-modal');
    }
}
