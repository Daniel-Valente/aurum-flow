<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\Reporte\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function dashboard(Request $request, ReportService $service)
    {
        $data = $service->dashboard(auth()->user, $request->all());

        return ApiResponse::success($data, 'Reporte generado');
    }
}
