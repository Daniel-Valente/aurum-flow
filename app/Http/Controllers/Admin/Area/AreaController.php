<?php

namespace App\Http\Controllers\Admin\Area;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Area\StoreAreaRequest;
use App\Http\Requests\Area\UpdateAreaRequest;
use App\Http\Resources\Area\AreaResource;
use App\Models\Area;
use App\Services\Area\AreaService;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::query();

        if ($request->filled('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $areas = $query->latest()->paginate(15);

        return ApiResponse::success([
            'items' => AreaResource::collection($areas->items()),
            'pagination' => [
                'total' => $areas->total(),
                'per_page' => $areas->perPage(),
                'current_page' => $areas->currentPage(),
                'last_page' => $areas->lastPage(),
            ]
        ], 'Lista de áreas');
    }

    public function list()
    {
        $areas = Area::where('estatus', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return ApiResponse::success($areas, 'Lista simple de áreas');
    }

    public function store(StoreAreaRequest $request, AreaService $service)
    {
        $area = $service->create($request->validated());

        return ApiResponse::success(
            new AreaResource($area),
            'Área creada',
            201
        );
    }

    public function update(UpdateAreaRequest $request, Area $area, AreaService $service)
    {
        $area = $service->update($area, $request->validated());

        return ApiResponse::success(
            new AreaResource($area),
            'Área actualizada'
        );
    }

    public function destroy(Area $area, AreaService $service)
    {
        $service->delete($area);

        return ApiResponse::success(null, 'Área eliminada');
    }

    public function toggle(Area $area)
    {
        $area->update([
            'estatus' => !$area->estatus
        ]);

        return ApiResponse::success(
            ['estatus' => $area->estatus],
            'Estado actualizado'
        );
    }
}
