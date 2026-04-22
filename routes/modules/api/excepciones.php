<?php

use App\Http\Controllers\GastoExcepcionController;

Route::prefix('excepciones')->group(function () {

    Route::get('/', [GastoExcepcionController::class, 'index'])
        ->middleware('permission:excepciones.ver|excepciones.ver.todas');

    Route::get('/{excepcion}', [GastoExcepcionController::class, 'show'])
        ->middleware('permission:excepciones.ver|excepciones.ver.todas');

    Route::post('/{excepcion}/resolver', [GastoExcepcionController::class, 'resolver'])
        ->middleware(
            'permission:excepciones.aprobar.nivel1|excepciones.aprobar.nivel2|excepciones.rechazar.nivel1|excepciones.rechazar.nivel2'
        );
});
