<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Gasto\StoreGastoRequest;
use App\Services\Gasto\GastoService;
use Illuminate\Http\Request;

class GastoController extends Controller
{
    public function store(StoreGastoRequest $request, GastoService $service)
    {
        $resultado = $service->crear($request->validated());

        if ($resultado['error']) {
            return ApiResponse::error($resultado['mensaje'], 422);
        }

        return ApiResponse::success(
            $resultado['gasto'],
            $resultado['mensaje']
        );
    }
}
