<?php

namespace App\Imports;

use App\Models\Area;
use App\Models\CentroCosto;
use App\Models\User;
use App\Services\Empleado\EmpleadoService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

class EmpleadosImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public Collection $preview;   // todas las filas parseadas con su estatus
    public bool $soloValidar;     // true = preview, false = commit

    private Collection $roles;
    private Collection $areas;
    private Collection $centrosCostos;

    public function __construct(bool $soloValidar = true)
    {
        $this->soloValidar   = $soloValidar;
        $this->preview       = collect();

        // Carga catálogos una sola vez
        $this->roles          = Role::all()->keyBy(fn($r) => strtolower($r->name));
        $this->centrosCostos  = CentroCosto::all()->keyBy(fn($c) => strtolower($c->nombre));
        $this->areas          = Area::all()->keyBy(fn($a) => strtolower($a->nombre));
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $fila   = $index + 2; // +2 porque row 1 = encabezados
            $errores = $this->validarFila($row, $fila);
            $estado  = empty($errores) ? 'ok' : 'error';

            $this->preview->push([
                'fila'            => $fila,
                'nombre_completo' => $row['nombre_completo'] ?? '',
                'email'           => $row['email'] ?? '',
                'rol'             => $row['rol'] ?? '',
                'area'            => $row['area'] ?? '',
                'centro_costo'    => $row['centro_costo'] ?? '',
                'puesto'          => $row['puesto'] ?? '',
                'estado'          => $estado,
                'errores'         => $errores,
                '_row'            => $row,   // datos crudos para el commit
            ]);

            // Solo persiste si no es modo preview y no hay errores
            if (!$this->soloValidar && $estado === 'ok') {
                $this->commit($row);
            }
        }
    }

    private function validarFila(Collection $row, int $fila): array
    {
        $errores = [];

        // Campos requeridos simples
        $requeridos = [
            'nombre_completo' => 'Nombre completo',
            'email'           => 'Email',
            'rol'             => 'Rol',
            'area'            => 'Área',
            'centro_costo'    => 'Centro de costo',
            'puesto'          => 'Puesto',
            'rfc'             => 'RFC',
            'curp'            => 'CURP',
            'nss'             => 'NSS',
            'numero_nomina'   => 'Número de nómina',
            'banco_nomina'    => 'Banco',
            'cuenta_nomina'   => 'Cuenta',
            'clabe_nomina'    => 'CLABE',
        ];

        foreach ($requeridos as $campo => $label) {
            if (empty(trim((string) ($row[$campo] ?? '')))) {
                $errores[] = "{$label} es obligatorio";
            }
        }

        // Email válido y único
        if (!empty($row['email'])) {
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'Email no válido';
            } elseif (User::where('email', $row['email'])->exists()) {
                $errores[] = "Email '{$row['email']}' ya está registrado";
            }
        }

        // Catálogos por nombre
        if (!empty($row['rol']) && !$this->roles->has(strtolower($row['rol']))) {
            $errores[] = "Rol '{$row['rol']}' no existe (opciones: " . $this->roles->keys()->join(', ') . ')';
        }
        if (!empty($row['area']) && !$this->areas->has(strtolower($row['area']))) {
            $errores[] = "Área '{$row['area']}' no existe";
        }
        if (!empty($row['centro_costo']) && !$this->centrosCostos->has(strtolower($row['centro_costo']))) {
            $errores[] = "Centro de costo '{$row['centro_costo']}' no existe";
        }

        // Longitudes exactas
        $longitudes = ['rfc' => 13, 'curp' => 18, 'clabe_nomina' => 18];
        foreach ($longitudes as $campo => $len) {
            $valor = trim((string) ($row[$campo] ?? ''));
            if (!empty($valor) && strlen($valor) !== $len) {
                $errores[] = strtoupper($campo) . " debe tener exactamente {$len} caracteres (tiene " . strlen($valor) . ')';
            }
        }

        // Fecha
        if (!empty($row['fecha_ingreso'])) {
            try {
                Carbon::parse($row['fecha_ingreso']);
            } catch (\Exception) {
                $errores[] = 'Fecha de ingreso inválida (usa YYYY-MM-DD)';
            }
        }

        // Límite crédito
        if (!empty($row['limite_credito']) && !is_numeric($row['limite_credito'])) {
            $errores[] = 'Límite de crédito debe ser numérico';
        }

        return $errores;
    }

    private function commit(Collection $row): void
    {
        app(EmpleadoService::class)->create([
            'nombre_completo' => $row['nombre_completo'],
            'email'           => $row['email'],
            'rol'             => $this->roles->get(strtolower($row['rol'])),
            'area_id'         => $this->areas->get(strtolower($row['area']))?->id,
            'centro_costo_id' => $this->centrosCostos->get(strtolower($row['centro_costo']))?->id,
            'puesto'          => $row['puesto'],
            'rfc'             => strtoupper(trim($row['rfc'])),
            'curp'            => strtoupper(trim($row['curp'])),
            'nss'             => $row['nss'],
            'numero_nomina'   => $row['numero_nomina'],
            'banco_nomina'    => $row['banco_nomina'],
            'cuenta_nomina'   => $row['cuenta_nomina'],
            'clabe_nomina'    => $row['clabe_nomina'],
            'telefono'        => $row['telefono'] ?? null,
            'fecha_ingreso'   => $row['fecha_ingreso'] ?? null,
            'tarjeta_credito_corporativa_asignada' => strtolower($row['tarjeta_corporativa'] ?? '') === 'si',
            'limite_credito_tarjeta' => !empty($row['limite_credito']) ? $row['limite_credito'] : null,
            'estatus'         => true,
        ]);
    }
}
