<?php

namespace App\Services\Proyecto;

use App\Helpers\FolioHelper;
use App\Models\Proyecto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProyectoService
{
    private const ALLOWED_SORT_COLUMNS = [
        'nombre', 'codigo', 'tipo', 'region', 'cuidad',
        'estado', 'pais',
        'estado_operativo', 'fecha_inicio', 'fecha_fin',
        'presupuesto_total', 'created_at',
    ];
    private const ALLOWED_SORT_DIRS  = ['asc', 'desc'];
    private const LIST_CACHE_KEY     = 'proyectos.list.activos';
    private const LIST_CACHE_TTL     = 600; // 10 min

    // -------------------------------------------------------------------------
    // Listado paginado con filtros
    // -------------------------------------------------------------------------

    public function paginate(
        string  $search           = '',
        string  $estatus          = '',
        string  $tipo             = '',
        string  $estadoOperativo  = '',
        string  $region           = '',
        ?int    $centroCostoId    = null,
        ?int    $responsableId    = null,
        string  $sortBy           = 'created_at',
        string  $sortDir          = 'desc',
        int     $perPage          = 15,
    ): LengthAwarePaginator {
        $sortBy  = in_array($sortBy,  self::ALLOWED_SORT_COLUMNS, true) ? $sortBy  : 'created_at';
        $sortDir = in_array($sortDir, self::ALLOWED_SORT_DIRS,    true) ? $sortDir : 'desc';
        $perPage = min($perPage, 100);

        return Proyecto::query()
            ->leftJoin('centros_costos', 'centros_costos.id', '=', 'proyectos.centro_costo_id')
            ->leftJoin('empleados',      'empleados.id',      '=', 'proyectos.responsable_id')
            ->select(
                'proyectos.*',
                'centros_costos.nombre  AS centro_costo_nombre',
                'empleados.nombre_completo AS responsable_nombre',
            )
            ->when($search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('proyectos.nombre',  'ilike', "%{$search}%")
                       ->orWhere('proyectos.codigo', 'ilike', "%{$search}%")
                       ->orWhere('proyectos.cliente','ilike', "%{$search}%")
                )
            )
            ->when($estatus !== '', fn($q) =>
                $q->where('proyectos.estatus', $estatus)
            )
            ->when($tipo, fn($q) =>
                $q->where('proyectos.tipo', $tipo)
            )
            ->when($estadoOperativo, fn($q) =>
                $q->where('proyectos.estado_operativo', $estadoOperativo)
            )
            ->when($region, fn($q) =>
                $q->where('proyectos.region', $region)
            )
            ->when($centroCostoId, fn($q) =>
                $q->where('proyectos.centro_costo_id', $centroCostoId)
            )
            ->when($responsableId, fn($q) =>
                $q->where('proyectos.responsable_id', $responsableId)
            )
            ->orderBy("proyectos.{$sortBy}", $sortDir)
            ->paginate($perPage);
    }

    public function list(): array
    {
        return Cache::remember(self::LIST_CACHE_KEY, self::LIST_CACHE_TTL, fn() =>
            Proyecto::where('estatus', true)
                ->where('estado_operativo', 'Activo')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo'])
                ->toArray()
        );
    }

    public function create(array $data): Proyecto
    {
        $codigo = match($data['tipo']) {
            'Ruta' => FolioHelper::generar('RT'),
            'Zona' => FolioHelper::generar('ZN'),
            default => FolioHelper::generar('PRY')
        };

        $proyecto = Proyecto::create([
            'codigo'           => $codigo,
            'nombre'           => $data['nombre'],
            'cliente'          => $data['cliente']          ?? null,
            'tipo'             => $data['tipo'],
            'descripcion'      => $data['descripcion']      ?? null,
            'region'           => $data['region']           ?? null,
            'estado_operativo' => $data['estado_operativo'] ?? 'Draft',
            'centro_costo_id'  => $data['centro_costo_id'] ?? null,
            'responsable_id'   => $data['responsable_id']  ?? null,
            'presupuesto_total'=> $data['presupuesto_total'] ?? null,
            'fecha_inicio'     => $data['fecha_inicio']     ?? null,
            'fecha_fin'        => $data['fecha_fin']        ?? null,
            'pais'             => $data['pais']             ?? null,
            'estado'           => $data['estado']           ?? null,
            'ciudad'           => $data['ciudad']           ?? null,
            'estatus'          => $data['estatus']          ?? true,
        ]);

        $this->flushCache();

        return $proyecto->load('centroCosto', 'responsable');
    }

    public function update(Proyecto $proyecto, array $data): Proyecto
    {
        $proyecto->update([
            'codigo'            => strtoupper(trim($data['codigo'])),
            'nombre'            => $data['nombre'],
            'cliente'           => $data['cliente']           ?? $proyecto->cliente,
            'tipo'              => $data['tipo'],
            'descripcion'       => $data['descripcion']       ?? $proyecto->descripcion,
            'region'            => $data['region']            ?? $proyecto->region,
            'estado_operativo'  => $data['estado_operativo']  ?? $proyecto->estado_operativo,
            'centro_costo_id'   => $data['centro_costo_id']  ?? $proyecto->centro_costo_id,
            'responsable_id'    => $data['responsable_id']   ?? $proyecto->responsable_id,
            'presupuesto_total' => $data['presupuesto_total'] ?? $proyecto->presupuesto_total,
            'fecha_inicio'      => $data['fecha_inicio']      ?? $proyecto->fecha_inicio,
            'fecha_fin'         => $data['fecha_fin']         ?? $proyecto->fecha_fin,
            'pais'              => $data['pais']              ?? $proyecto->pais,
            'estado'            => $data['estado']            ?? $proyecto->estado,
            'ciudad'            => $data['ciudad']            ?? $proyecto->ciudad,
            'estatus'           => $data['estatus']           ?? $proyecto->estatus,
        ]);

        $this->flushCache();

        return $proyecto->load('centroCosto', 'responsable');
    }

    public function delete(Proyecto $proyecto): bool
    {
        $proyecto->delete();
        $this->flushCache();

        return true;
    }

    public function toggleEstatus(Proyecto $proyecto): Proyecto
    {
        $proyecto->update(['estatus' => !$proyecto->estatus]);
        $this->flushCache();

        return $proyecto->fresh();
    }

    // -------------------------------------------------------------------------
    // Valores únicos para poblar filtros en la UI — cacheados
    // -------------------------------------------------------------------------

    public function tipos(): array
    {
        return Cache::remember('proyectos.tipos', self::LIST_CACHE_TTL, fn() =>
            Proyecto::whereNotNull('tipo')
                ->distinct()
                ->orderBy('tipo')
                ->pluck('tipo')
                ->toArray()
        );
    }

    public function regiones(): array
    {
        return Cache::remember('proyectos.regiones', self::LIST_CACHE_TTL, fn() =>
            Proyecto::query()
                ->select('region')
                ->whereNotNull('region')
                ->whereRaw("TRIM(region) != ''")
                ->distinct()
                ->orderBy('region')
                ->pluck('region')
                ->filter()
                ->values()
                ->toArray()
        );
    }

    public function ciudades(): array
    {
        return Cache::remember('proyectos.ciudades', self::LIST_CACHE_TTL, fn() =>
            Proyecto::query()
                ->select('ciudad')
                ->whereNotNull('ciudad')
                ->whereRaw("TRIM(ciudad) != ''")
                ->distinct()
                ->orderBy('ciudad')
                ->pluck('ciudad')
                ->filter()
                ->values()
                ->toArray()
        );
    }

    public function estados(): array
    {
        return Cache::remember('proyectos.estados', self::LIST_CACHE_TTL, fn() =>
            Proyecto::query()
                ->select('estado')
                ->whereNotNull('estado')
                ->whereRaw("TRIM(estado) != ''")
                ->distinct()
                ->orderBy('estado')
                ->pluck('estado')
                ->filter()
                ->values()
                ->toArray()
        );
    }

    public function paises(): array
    {
        return Cache::remember('proyectos.paises', self::LIST_CACHE_TTL, fn() =>
            Proyecto::query()
                ->select('pais')
                ->whereNotNull('pais')
                ->whereRaw("TRIM(pais) != ''")
                ->distinct()
                ->orderBy('pais')
                ->pluck('pais')
                ->filter()
                ->values()
                ->toArray()
        );
    }

    private function flushCache(): void
    {
        Cache::forget(self::LIST_CACHE_KEY);
        Cache::forget('proyectos.tipos');
        Cache::forget('proyectos.regiones');
        Cache::forget('proyectos.ciudades');
        Cache::forget('proyectos.estados');
        Cache::forget('proyectos.paises');
    }
}
