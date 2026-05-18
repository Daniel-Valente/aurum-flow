<?php

namespace App\Livewire\Empresas;

use App\Models\Empresa;
use App\Services\Empresa\EmpresaService;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormModal extends Component
{
    public ?int $editingId = null;

    public string $nombre = '';
    public string $nombre_comercial = '';
    public string $rfc = '';

    public string $domicilio_fiscal = '';
    public string $ciudad = '';
    public string $estado = '';
    public string $codigo_postal = '';
    public string $pais = '';

    public string $telefono = '';
    public string $email = '';
    public string $sitio_web = '';

    public string $moneda = '';
    public string $time_zone = '';
    public string $logo_path = '';

    public string $notas = '';

    #[On('openEmpresaForm')]
    public function open(?int $id = null): void
    {
        if ($id) {
            $empresa = Empresa::findOrFail($id);
            $this->editingId = $id;
            $this->nombre    = $empresa->nombre;
            $this->nombre_comercial = $empresa->nombre_comercial;
            $this->rfc =  $empresa->rfc;
            $this->domicilio_fiscal =  $empresa->domicilio_fiscal;
            $this->ciudad =  $empresa->ciudad;
            $this->estado =  $empresa->estado;
            $this->codigo_postal =  $empresa->codigo_postal;
            $this->pais =  $empresa->pais;
            $this->telefono =  $empresa->telefono;
            $this->email =  $empresa->email;
            $this->sitio_web =  $empresa->sitio_web;
            $this->moneda =  $empresa->moneda;
            $this->time_zone =  $empresa->timezone;
            $this->logo_path =  $empresa->logo_path;
        } else {
            $this->resetForm();
        }

        $this->resetValidation();
        $this->modal('empresa-form')->show();
    }

    public function save(EmpresaService $service): void
    {
        $this->telefono = preg_replace('/\D/', '', $this->telefono);
        $this->validate([
            'nombre' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'rfc' => 'required|string|min:13|max:13',
            'domicilio_fiscal' => 'required|string',
            'ciudad' => 'required|string|max:100',
            'estado' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:10',
            'pais' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:10',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('empresas', 'email')->ignore(
                    optional(Empresa::find($this->editingId))->id
                ),
            ],
            'sitio_web' => 'nullable|string|max:255',
            'logo_path' => 'nullable|string|max:500',
            'notas' => 'nullable|string|max:500',

        ], messages: [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',

            // Nombre comercial
            'nombre_comercial.string' => 'El nombre comercial debe ser texto.',
            'nombre_comercial.max' => 'El nombre comercial no puede exceder 255 caracteres.',

            // RFC
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.string' => 'El RFC debe ser texto.',
            'rfc.min' => 'El RFC debe tener exactamente 13 caracteres.',
            'rfc.max' => 'El RFC debe tener exactamente 13 caracteres.',

            // Domicilio fiscal
            'domicilio_fiscal.required' => 'El domicilio fiscal es obligatorio.',
            'domicilio_fiscal.string' => 'El domicilio fiscal debe ser texto.',

            // Ciudad
            'ciudad.required' => 'La ciudad es obligatoria.',
            'ciudad.string' => 'La ciudad debe ser texto.',
            'ciudad.max' => 'La ciudad no puede exceder 100 caracteres.',

            'estado.required' => 'El estado es obligatorio.',
            'estado.string' => 'El estado debe ser texto.',
            'estado.max' => 'El estado no puede exceder 100 caracteres.',

            'codigo_postal.required' => 'El código postal es obligatorio.',
            'codigo_postal.string' => 'El código postal debe ser texto.',
            'codigo_postal.max' => 'El código postal no puede exceder 10 caracteres.',

            'pais.required' => 'El país es obligatorio.',
            'pais.string' => 'El país debe ser texto.',
            'pais.max' => 'El país no puede exceder 100 caracteres.',

            'telefono.string' => 'El teléfono debe ser texto.',
            'telefono.max' => 'El teléfono no puede exceder 10 caracteres.',

            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no tiene un formato válido.',
            'email.max' => 'El email no puede exceder 255 caracteres.',
            'email.unique' => 'Este email ya está registrado.',

            'sitio_web.string' => 'El sitio web debe ser texto.',
            'sitio_web.max' => 'El sitio web no puede exceder 255 caracteres.',

            'logo_path.string' => 'La ruta del logo debe ser texto.',
            'logo_path.max' => 'La ruta del logo no puede exceder 500 caracteres.',

            'notas.string' => 'Las notas deben ser texto.',
            'notas.max' => 'Las notas no pueden exceder 500 caracteres.',
        ]);

        $data = [
            'nombre' => $this->nombre,
            'nombre_comercial' => $this->nombre_comercial,
            'rfc' => $this->rfc,
            'domicilio_fiscal' => $this->domicilio_fiscal,
            'ciudad' => $this->ciudad,
            'estado' => $this->estado,
            'codigo_postal' => $this->codigo_postal,
            'pais' => $this->pais,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'sitio_web' => $this->sitio_web,
            'moneda' => 'MXN',
            'timezone' => 'America/Mexico_City',
            'activo' => true,
            'logo_path' => $this->logo_path,
            'notas' => $this->notas,
        ];

        if ($this->editingId) {
            $service->update(Empresa::findOrFail($this->editingId), $data, auth()->user());
            $msg = 'Empresa actualizada correctamente.';
        } else {
            $service->create($data, auth()->user());
            $msg = 'Empresa creada correctamente.';
        }

        $this->modal('empresa-form')->close();
        $this->resetForm();
        $this->dispatch('empresaSaved', message: $msg);
    }

    private function resetForm(): void
    {
        $this->reset(['nombre', 'nombre_comercial', 'rfc', 'domicilio_fiscal', 'ciudad', 'estado', 'codigo_postal', 'pais', 'telefono', 'email', 'sitio_web', 'moneda', 'time_zone', 'logo_path', 'notas']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.empresas.form-modal');
    }
}
