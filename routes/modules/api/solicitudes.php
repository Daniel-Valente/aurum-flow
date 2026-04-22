<?php

use App\Http\Controllers\SolicitudController;

Route::prefix('solicitudes')->group(function () {

    Route::post('/', [SolicitudController::class, 'store'])
        ->middleware('permission:solicitudes.crear');

    Route::get('/', [SolicitudController::class, 'index'])
        ->middleware('permission:solicitudes.ver.propias|solicitudes.ver.todas');

    Route::get('/{solicitud}', [SolicitudController::class, 'show'])
        ->middleware('permission:solicitudes.ver.propias|solicitudes.ver.todas');

    Route::post('/{solicitud}/detalle', [SolicitudController::class, 'agregarDetalle'])
        ->middleware('permission:solicitudes.editar');

    Route::post('/{solicitud}/enviar', [SolicitudController::class, 'enviar'])
        ->middleware('permission:solicitudes.enviar');

    Route::post('/{solicitud}/resolver', [SolicitudController::class, 'resolver'])
        ->middleware('permission:solicitudes.aprobar|solicitudes.rechazar');

    Route::patch('/{solicitud}/cancelar', [SolicitudController::class, 'cancelar'])
        ->middleware('permission:solicitudes.eliminar');

    Route::patch('/{solicitud}/reabrir', [SolicitudController::class, 'reabrir'])
        ->middleware('permission:solicitudes.editar');

    Route::patch('/{solicitud}/aprobar', [SolicitudController::class, 'aprobar'])
        ->middleware('permission:solicitudes.aprobar');
});
