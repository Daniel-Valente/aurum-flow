<?php

namespace App\Livewire\Empresas;

use App\Models\ConfiguracionEmpresa;
use App\Models\Empresa;
use App\Services\Empleado\EmpleadoService;
use App\Services\Empresa\ConfiguracionEmpresaService;
use Livewire\Attributes\On;
use Livewire\Component;

class ConfiguracionModal extends Component
{
    public ?int $empresaId = null;
    public ?Empresa $empresa = null;

    public ?int $dias_habiles_comprobacion = null;
    public ?int $cfdi_dias_antes_permitidos = null;
    public ?int $cfdi_dias_despues_permitidos = null;

    public string $rfc_empresa = '';
    public bool $validar_rfc_receptor = true;

    public bool $propina_auto_aprueba = true;
    public bool $gasto_compartido_auto_aprueba = true;
    public bool $gasto_cliente_auto_aprueba = true;

    public string $validador_tickets = 'finanzas';

    public string $moneda = '';
    public string $pais = '';

    public ?ConfiguracionEmpresa $configActual = null;
    public bool $esConfiguracionPropia = false;
    public array $valoresGlobales = [];
    public array $roles           = [];

    public function mount(EmpleadoService $service)
    {
        $this->roles = $service->roles();
    }

    #[On('openConfiguracionModal')]
    public function open(int $empresaId): void
    {
        $this->empresaId = $empresaId;
        $this->empresa = Empresa::findOrFail($empresaId);

        $service = app(ConfiguracionEmpresaService::class);

        $this->configActual = $service->obtenerPorEmpresa($this->empresa);
        $this->esConfiguracionPropia = $this->configActual->empresa_id === $this->empresa->id;

        $globalConfig = $service->obtenerGlobal();
        $this->valoresGlobales = [
            'dias_habiles_comprobacion'     => $globalConfig->dias_habiles_comprobacion,
            'cfdi_dias_antes_permitidos'    => $globalConfig->cfdi_dias_antes_permitidos,
            'cfdi_dias_despues_permitidos'  => $globalConfig->cfdi_dias_despues_permitidos,
            'validar_rfc_receptor'          => $globalConfig->validar_rfc_receptor,
            'propina_auto_aprueba'          => $globalConfig->propina_auto_aprueba,
            'gasto_compartido_auto_aprueba' => $globalConfig->gasto_compartido_auto_aprueba,
            'gasto_cliente_auto_aprueba'    => $globalConfig->gasto_cliente_auto_aprueba,
            'validador_tickets'             => $globalConfig->validador_tickets,
            'rfc_empresa'                   => $globalConfig->rfc_empresa
        ];

        $this->cargarDatos();
        $this->resetValidation();
        $this->modal('configuracion-form')->show();
    }

    private function cargarDatos(): void
    {
        $this->dias_habiles_comprobacion = $this->configActual->dias_habiles_comprobacion;
        $this->cfdi_dias_antes_permitidos = $this->configActual->cfdi_dias_antes_permitidos;
        $this->cfdi_dias_despues_permitidos = $this->configActual->cfdi_dias_despues_permitidos;
        $this->rfc_empresa = $this->configActual->rfc_empresa ??  $this->empresa->rfc;
        $this->validar_rfc_receptor = $this->configActual->validar_rfc_receptor;
        $this->propina_auto_aprueba = $this->configActual->propina_auto_aprueba;
        $this->gasto_compartido_auto_aprueba = $this->configActual->gasto_compartido_auto_aprueba;
        $this->gasto_cliente_auto_aprueba = $this->configActual->gasto_cliente_auto_aprueba;
        $this->validador_tickets = $this->configActual->validador_tickets;
        $this->moneda = $this->configActual->moneda ?? $this->empresa->moneda;
        $this->pais = $this->configActual->pais ?? '';
    }

    public function save(ConfiguracionEmpresaService $service): void
    {
        $this->validate([
            'dias_habiles_comprobacion'     => 'required|integer|min:1|max:30',
            'cfdi_dias_antes_permitidos'    => 'required|integer|min:0|max:60',
            'cfdi_dias_despues_permitidos'  => 'required|integer|min:0|max:60',
            'rfc_empresa'                   => 'required|string|min:13|max:13',
            'validar_rfc_receptor'          => 'boolean',
            'propina_auto_aprueba'          => 'boolean',
            'gasto_compartido_auto_aprueba' => 'boolean',
            'gasto_cliente_auto_aprueba'    => 'boolean',
            'validador_tickets'             => 'required|string|max:20',
            'moneda'                        => 'nullable|string|size:3',
            'pais'                          => 'nullable|string|size:2',
        ], messages: [
            'dias_habiles_comprobacion.required' => 'Los días hábiles son requeridos.',
            'dias_habiles_comprobacion.integer'  => 'Debe ser un número entero.',
            'dias_habiles_comprobacion.min'      => 'Mínimo 1 día.',
            'dias_habiles_comprobacion.max'      => 'Máximo 30 días.',

            'cfdi_dias_antes_permitidos.required' => 'Campo requerido.',
            'cfdi_dias_antes_permitidos.integer'  => 'Debe ser un número entero.',
            'cfdi_dias_antes_permitidos.min'      => 'Mínimo 0 días.',
            'cfdi_dias_antes_permitidos.max'      => 'Máximo 60 días.',

            'cfdi_dias_despues_permitidos.required' => 'Campo requerido.',
            'cfdi_dias_despues_permitidos.integer'  => 'Debe ser un número entero.',
            'cfdi_dias_despues_permitidos.min'      => 'Mínimo 0 días.',
            'cfdi_dias_despues_permitidos.max'      => 'Máximo 60 días.',

            'rfc_empresa.required' => 'El RFC es obligatorio.',
            'rfc_empresa.string' => 'El RFC debe ser texto.',
            'rfc_empresa.min' => 'El RFC debe tener exactamente 13 caracteres.',
            'rfc_empresa.max' => 'El RFC debe tener exactamente 13 caracteres.',

            'validador_tickets.required' => 'El validador de tickets es requerido.',
            'validador_tickets.string'   => 'Debe ser texto.',
            'validador_tickets.max'      => 'No puede exceder 20 caracteres.',

            'moneda.size' => 'La moneda debe tener 3 caracteres.',
            'pais.size'   => 'El país debe tener 2 caracteres.',
        ]);

        $data = [
            'dias_habiles_comprobacion'     => $this->dias_habiles_comprobacion,
            'cfdi_dias_antes_permitidos'    => $this->cfdi_dias_antes_permitidos,
            'cfdi_dias_despues_permitidos'  => $this->cfdi_dias_despues_permitidos,
            'rfc_empresa'                   => empty($this->rfc_empresa) ? null : strtoupper($this->rfc_empresa),
            'validar_rfc_receptor'          => $this->validar_rfc_receptor,
            'propina_auto_aprueba'          => $this->propina_auto_aprueba,
            'gasto_compartido_auto_aprueba' => $this->gasto_compartido_auto_aprueba,
            'gasto_cliente_auto_aprueba'    => $this->gasto_cliente_auto_aprueba,
            'validador_tickets'             => $this->validador_tickets,
            'moneda'                        => empty($this->moneda) ? null : strtoupper($this->moneda),
            'pais'                          => empty($this->pais) ? null : strtoupper($this->pais),
        ];

        try {
            $service->actualizar($this->empresa, $data, auth()->user());

            $this->modal('configuracion-form')->close();
            $this->resetForm();
            $this->dispatch('configuracionGuardada', mensaje: 'Configuración actualizada correctamente.');
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function resetear(ConfiguracionEmpresaService $service): void
    {
        if (!$this->esConfiguracionPropia) {
            return;
        }

        try {
            $service->resetear($this->empresa, auth()->user());

            $this->modal('configuracion-form')->close();
            $this->resetForm();
            $this->dispatch('configuracionGuardada', mensaje: 'Configuración reseteada a la global.');
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'empresaId',
            'empresa',
            'dias_habiles_comprobacion',
            'cfdi_dias_antes_permitidos',
            'cfdi_dias_despues_permitidos',
            'rfc_empresa',
            'validar_rfc_receptor',
            'propina_auto_aprueba',
            'gasto_compartido_auto_aprueba',
            'gasto_cliente_auto_aprueba',
            'validador_tickets',
            'moneda',
            'pais',
            'configActual',
            'esConfiguracionPropia',
            'valoresGlobales',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.empresas.configuracion-modal');
    }
}
