<?php

namespace App\Livewire\Empleados;

use App\Models\Empleado;
use App\Services\Area\AreaService;
use App\Services\CentroCosto\CentroCostoService;
use App\Services\Empleado\EmpleadoService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class FormModal extends Component
{
    public ?int $editingId = null;
    public bool $esGerente = false;

    public array $roles         = [];
    public array $centrosCostos = [];
    public array $areas         = [];
    public array $empresas      = [];

    public string $nombre_completo = '';
    public string $email = '';
    public string $telefono = '';
    public string $rfc = '';
    public string $curp = '';
    public string $nss = '';
    public string $puesto = '';
    public ?int $area_id = null;
    public ?int $rol_id = null;
    public ?int $centro_costo_id = null;
    public ?int $empresa_id = null;
    public string $fecha_ingreso = '';
    public string $numero_nomina = '';
    public string $banco_nomina = '';
    public string $cuenta_nomina = '';
    public string $clabe_nomina = '';
    public bool $tarjeta_credito_corporativa_asignada = false;
    public ?string $limite_credito_tarjeta = null;

    #[On('openEmpleadoForm')]
    public function open(?int $id = null): void
    {
        $authUser     = auth()->user();
        $authEmpleado = $authUser->empleado;

        if ($id) {
            $empleado = Empleado::with('user.roles')->findOrFail($id);

            $this->editingId       = $empleado->id;
            $this->nombre_completo = $empleado->nombre_completo;
            $this->email           = $empleado->user?->email ?? '';
            $this->rol_id          = $empleado->user?->roles->first()?->id ?? '';
            $this->puesto          = $empleado->puesto ?? '';
            $this->area_id         = $empleado->area_id;
            $this->centro_costo_id = $empleado->centro_costo_id;
            $this->empresa_id      = $empleado->empresa_id;
            $this->rfc             = $empleado->rfc ?? '';
            $this->curp            = $empleado->curp ?? '';
            $this->numero_nomina   = $empleado->numero_nomina ?? '';
            $this->banco_nomina    = $empleado->banco_nomina ?? '';
            $this->cuenta_nomina   = $empleado->cuenta_nomina ?? '';
            $this->clabe_nomina    = $empleado->clabe_nomina ?? '';
            $this->nss             = $empleado->nss ?? '';
            $this->fecha_ingreso   = $empleado->fecha_ingreso?->format('Y-m-d') ?? '';
            $this->telefono        = $empleado->telefono ?? '';
            $this->tarjeta_credito_corporativa_asignada = $empleado->tarjeta_credito_corporativa_asignada ?? false;
            $this->limite_credito_tarjeta = $empleado->limite_credito_tarjeta ?? null;
        } else {
            $this->resetForm();

            if ($authUser->hasRole('gerente') && $authEmpleado?->centro_costo_id) {
                $this->centro_costo_id = $authEmpleado->centro_costo_id;
                $this->area_id         = $authEmpleado->area_id;

                $this->esGerente = true;
            }
        }

        $this->resetValidation();
        $this->modal('empleado-form')->show();
    }

    public function save(EmpleadoService $service): void
    {
        $this->telefono = preg_replace('/\D/', '', $this->telefono);

        $this->validate([
            'nombre_completo' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(
                    optional(Empleado::find($this->editingId))->user_id
                ),
            ],
            'rol_id'            => 'required|exists:roles,id',
            'area_id'           => 'required|exists:areas,id',
            'centro_costo_id' => 'required|exists:centros_costos,id',
            'empresa_id'      => 'required|exists:empresas,id',
            'rfc'  => 'required|string|min:13|max:13',
            'curp' => 'required|string|min:18|max:18',
            'puesto'        => 'required|string|max:100',
            'numero_nomina' => 'required|string|max:50',
            'banco_nomina'  => 'required|string|max:100',
            'cuenta_nomina' => 'required|string|max:50',
            'clabe_nomina'  => 'required|string|size:18',
            'nss'           => 'required|string|max:11',
            'telefono'      => 'nullable|string|max:10',
            'fecha_ingreso' => 'nullable|date',
            'limite_credito_tarjeta' => 'nullable|numeric|min:0',
            'tarjeta_credito_corporativa_asignada' => 'boolean'
        ], messages: [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',

            'email.required'           => 'El email es obligatorio.',
            'email.email'              => 'El email no tiene un formato válido.',
            'email.unique'             => 'Este email ya está registrado.',

            'rol_id.required'          => 'El rol es obligatorio.',
            'rol_id.exists'            => 'El rol seleccionado no es válido.',

            'centro_costo_id.required' => 'La referencia contable es obligatoria.',
            'centro_costo_id.exists'   => 'La referencia contable seleccionada no es válida.',

            'empresa_id.required'      => 'La empresa es obligatoria.',
            'empresa_id.exists'        => 'La empresa seleccionada no es válida.',

            'area_id.required'         => 'El área es obligatoria.',
            'area_id.exists'           => 'El área seleccionada no es válida.',

            'rfc.required'             => 'El RFC es obligatorio.',
            'rfc.min'                  => 'El RFC debe tener exactamente 13 caracteres.',
            'rfc.max'                  => 'El RFC debe tener exactamente 13 caracteres.',

            'curp.required'            => 'El CURP es obligatorio.',
            'curp.min'                 => 'El CURP debe tener exactamente 18 caracteres.',
            'curp.max'                 => 'El CURP debe tener exactamente 18 caracteres.',

            'clabe_nomina.size'        => 'La CLABE debe tener exactamente 18 dígitos.',
            'clabe_nomina.required'    => 'La CLABE es obligatoria.',

            'fecha_ingreso.date'       => 'La fecha de ingreso no tiene un formato válido.',
            'nss.required'             => 'El nss es obligatorio.',
            'puesto.required'          => 'El puesto es obligatorio.',
            'numero_nomina.required'   => 'El número de nómina es obligatorio.',
            'banco_nomina.required'    => 'El banco es obligatorio.',
            'cuenta_nomina.required'   => 'La cuenta de nómina es obligatoria.',
            'telefono.max'             => 'El Teléfono debe tener exactamente 10 caracteres.',

            'limite_credito_tarjeta.numeric'  => 'El límite de crédito debe ser un número.',
            'limite_credito_tarjeta.min'      => 'El límite de crédito no puede ser negativo.',

        ]);

        $data = [
            'nombre_completo' => $this->nombre_completo,
            'email' => $this->email,
            'puesto' => $this->puesto,
            'area_id' => $this->area_id,
            'centro_costo_id' => $this->centro_costo_id,
            'empresa_id' => $this->empresa_id,
            'rfc' => $this->rfc,
            'curp' => $this->curp,
            'numero_nomina' => $this->numero_nomina,
            'banco_nomina' => $this->banco_nomina,
            'cuenta_nomina' => $this->cuenta_nomina,
            'clabe_nomina' => $this->clabe_nomina,
            'rol' => Role::findById($this->rol_id),
            'nss' => $this->nss,
            'fecha_ingreso' => $this->fecha_ingreso,
            'telefono' => $this->telefono,
            'tarjeta_credito_corporativa_asignada' => $this->tarjeta_credito_corporativa_asignada,
            'limite_credito_tarjeta' => $this->limite_credito_tarjeta,
            'estatus' => true,
        ];

        if ($this->editingId) {
            $service->update(Empleado::findOrFail($this->editingId), $data);
            $msg = 'Empleado actualizado correctamente.';
        } else {
            $service->create($data);
            $msg = 'Empleado creada correctamente.';
        }

        $this->modal('empleado-form')->close();
        $this->resetForm();
        $this->dispatch('empleadoSaved', message: $msg);
    }

    public function mount(EmpleadoService $empleadoService, CentroCostoService $centroService, AreaService $areaService)
    {
        $this->roles = $empleadoService->roles();
        $this->centrosCostos = $centroService->list();
        $this->areas = $areaService->list();
    }

    public function render()
    {
        return view('livewire.empleados.form-modal');
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'nombre_completo', 'email', 'telefono', 'rfc', 'curp', 'nss', 'puesto', 'area_id', 'rol_id', 'centro_costo_id', 'empresa_id', 'fecha_ingreso', 'numero_nomina', 'banco_nomina', 'cuenta_nomina', 'clabe_nomina' ]);
        $this->resetValidation();
    }
}
