<?php

use App\Http\Controllers\GastoComprobanteController;
use App\Http\Controllers\GastoController;

Route::prefix('gastos')->group(function () {

    Route::post('/', [GastoController::class, 'store'])
        ->middleware('permission:gastos.crear');

    Route::get('/{gasto}/timeline', [GastoController::class, 'timeline'])
        ->middleware('permission:gastos.ver.propios|gastos.ver.todos');

    Route::post('/{gasto}/comprobante', [GastoController::class, 'subirComprobante'])
        ->middleware('permission:gastos.subir.comprobante');

    /*Route::get('/comprobantes/{comprobante}/download', [GastoComprobanteController::class, 'download'])
        ->middleware('permission:gastos.ver.propios|gastos.ver.todos');*/
});
