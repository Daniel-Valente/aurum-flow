<?php

use App\Http\Controllers\GastoComprobanteController;
use App\Livewire\Tarjeta\Index;
use App\Livewire\Tarjeta\Show;

Route::prefix('comprobacion-tarjeta')->name('tarjetas.')->group(function () {
    Route::get('/', Index::class)
        ->name('index');

    Route::get('/{comprobacion}', Show::class)
        ->name('show');

    Route::get('/comprobantes/{comprobante}/descargar', [GastoComprobanteController::class, 'download'])
        ->name('comprobantes.descargar');
});
