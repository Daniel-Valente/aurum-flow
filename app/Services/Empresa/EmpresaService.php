<?php

namespace App\Services\Empresa;

use App\Helpers\FolioHelper;
use App\Models\Empresa;
use App\Services\Auditoria\ActividadLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Storage;

class EmpresaService
{
    public function __construct(
        private ActividadLogService $actividadLog
    ) {}

    private const ALLOWED_SORT_COLUMNS = ['nombre', 'rfc', 'ciudad', 'estatus', 'created_at'];
    private const ALLOWED_SORT_DIRS    = ['asc', 'desc'];

    public function paginate(
        string $search  = '',
        bool   $soloActivas = false,
        string $sortBy  = 'nombre',
        string $sortDir = 'asc',
        int    $perPage = 15,
    ): LengthAwarePaginator {
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'nombre';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'asc';
        $perPage = min($perPage, 100);

        return Empresa::query()
            ->withCount(['empleados', 'proyectos', 'areas'])
            ->when($search, fn($q) =>
                $q->where(function ($q2) use ($search) {
                    $q2->where('nombre',          'ilike', "%{$search}%")
                       ->orWhere('nombre_comercial', 'ilike', "%{$search}%")
                       ->orWhere('rfc',             'ilike', "%{$search}%")
                       ->orWhere('ciudad',           'ilike', "%{$search}%");
                })
            )
            ->when($soloActivas, fn($q) => $q->where('activo', true))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    public function list(bool $soloActivas = true): array
    {
        return Empresa::query()
            ->when($soloActivas, fn($q) => $q->where('activo', true))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'nombre_comercial', 'rfc', 'moneda'])
            ->toArray();
    }

    public function create(array $data, $user): Empresa
    {
        if (!$user->can('empresas.crear')) {
            throw new AuthorizationException('No autorizado para crear empresas');
        }

        $this->validarRfcUnico($data['rfc'] ?? null);

        return DB::transaction(function () use ($data, $user) {
            $logoPath = null;
            if (!empty($data['logo'])) {
                $logoPath = $this->guardarLogo($data['logo']);
            }

            $empresa = Empresa::create([
                'codigo'           => FolioHelper::generar('EMP'),
                'nombre'           => $data['nombre'],
                'nombre_comercial' => $data['nombre_comercial'] ?? null,
                'rfc'              => strtoupper(trim($data['rfc'] ?? '')),
                'domicilio_fiscal' => $data['domicilio_fiscal'] ?? null,
                'ciudad'           => $data['ciudad'] ?? null,
                'estado'           => $data['estado'] ?? null,
                'codigo_postal'    => $data['codigo_postal'] ?? null,
                'pais'             => $data['pais'] ?? 'MX',
                'telefono'         => $data['telefono'] ?? null,
                'email'            => $data['email'] ?? null,
                'sitio_web'        => $data['sitio_web'] ?? null,
                'moneda'           => $data['moneda'] ?? 'MXN',
                'timezone'         => $data['timezone'] ?? 'America/Mexico_City',
                'logo_path'        => $logoPath,
                'activo'           => true,
                'notas'            => $data['notas'] ?? null,
            ]);

            $this->actividadLog->registrar([
                'user'                => $user,
                'evento'              => 'created',
                'modulo'              => 'empresas',
                'entidad'             => $empresa,
                'entidad_descripcion' => "Empresa {$empresa->nombre}",
                'datos_despues'       => $empresa->toArray(),
                'es_sensible'         => true,
            ]);

            return $empresa;
        });
    }

    public function update(Empresa $empresa, array $data, $user): Empresa
    {
        if (!$user->can('empresas.editar')) {
            throw new AuthorizationException('No autorizado para editar empresas');
        }

        if (isset($data['rfc']) && strtoupper($data['rfc']) !== $empresa->rfc) {
            $this->validarRfcUnico($data['rfc'], $empresa->id);
        }

        return DB::transaction(function () use ($empresa, $data, $user) {
            $antes = $empresa->toArray();

            if (!empty($data['logo'])) {
                if ($empresa->logo_path) {
                    Storage::disk('public')->delete($empresa->logo_path);
                }
                $data['logo_path'] = $this->guardarLogo($data['logo']);
            }
            unset($data['logo']);
            if (isset($data['rfc'])) {
                $data['rfc'] = strtoupper(trim($data['rfc']));
            }

            $empresa->update($data);

            $this->actividadLog->registrar([
                'user'                => $user,
                'evento'              => 'updated',
                'modulo'              => 'empresas',
                'entidad'             => $empresa,
                'entidad_descripcion' => "Empresa {$empresa->nombre}",
                'datos_antes'         => $antes,
                'datos_despues'       => $empresa->fresh()->toArray(),
                'es_sensible'         => true,
            ]);

            return $empresa->fresh();
        });
    }

    public function toggleActivo(Empresa $empresa, $user): Empresa
    {
        if (!$user->can('empresas.editar')) {
            throw new AuthorizationException('No autorizado');
        }

        if ($empresa->activo && $empresa->empleados()->where('estatus', true)->exists()) {
            throw new \Exception(
                "No se puede desactivar la empresa. Tiene empleados activos. Desactívalos primero."
            );
        }

        $empresa->update(['activo' => !$empresa->activo]);

        $this->actividadLog->registrar([
            'user'                => $user,
            'evento'              => 'updated',
            'modulo'              => 'empresas',
            'entidad'             => $empresa,
            'entidad_descripcion' => "Empresa {$empresa->nombre} " . ($empresa->activo ? 'activada' : 'desactivada'),
            'es_sensible'         => true,
        ]);

        return $empresa->fresh();
    }

    public function delete(Empresa $empresa, $user): bool
    {
        if (!$user->can('empresas.eliminar')) {
            throw new AuthorizationException('No autorizado para eliminar empresas');
        }

        if ($empresa->empleados()->count() > 0) {
            throw new \Exception('No se puede eliminar una empresa con empleados registrados.');
        }

        if ($empresa->proyectos()->count() > 0) {
            throw new \Exception('No se puede eliminar una empresa con proyectos registrados.');
        }

        return DB::transaction(function () use ($empresa, $user) {
            $this->actividadLog->registrar([
                'user'                => $user,
                'evento'              => 'deleted',
                'modulo'              => 'empresas',
                'entidad'             => $empresa,
                'entidad_descripcion' => "Empresa {$empresa->nombre} eliminada",
                'datos_antes'         => $empresa->toArray(),
                'es_sensible'         => true,
            ]);

            if ($empresa->logo_path) {
                Storage::disk('public')->delete($empresa->logo_path);
            }

            $empresa->delete();

            return true;
        });
    }

    public function stats(Empresa $empresa): array
    {
        return [
            'empleados_activos' => $empresa->empleados()->where('estatus', true)->count(),
            'empleados_total'   => $empresa->empleados()->count(),
            'proyectos_activos' => $empresa->proyectos()->where('estatus', 'activo')->count(),
            'proyectos_total'   => $empresa->proyectos()->count(),
            'areas'             => $empresa->areas()->count(),
            'presupuesto_total' => $empresa->presupuestos()
                ->where('estatus', 'activo')
                ->sum('monto_total'),
            'presupuesto_disponible' => $empresa->presupuestos()
                ->where('estatus', 'activo')
                ->get()
                ->sum('monto_disponible'),
        ];
    }

    private function validarRfcUnico(?string $rfc, ?int $exceptId = null): void
    {
        if (empty($rfc)) return;

        $existe = Empresa::where('rfc', strtoupper(trim($rfc)))
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->exists();

        if ($existe) {
            throw new \Exception("Ya existe una empresa registrada con el RFC {$rfc}.");
        }
    }

    private function guardarLogo($file): string
    {
        return $file->store('empresas/logos', 'public');
    }
}
