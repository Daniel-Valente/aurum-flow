<?php

use App\Http\Controllers\GastoComprobanteController;
use App\Livewire\Gastos\Index;
use App\Livewire\Gastos\Show;
use Illuminate\Support\Facades\Route;

Route::prefix('gastos')->name('gastos.')->group(function () {
    Route::get('/', Index::class)
        ->name('index')
        ->middleware('permission:gastos.ver.propios|gastos.ver.todos');

    Route::get('/{solicitud}', Show::class)
        ->name('show')
        ->middleware('permission:gastos.ver.propios|gastos.ver.todos');

    Route::get('/comprobantes/{comprobante}/descargar', [GastoComprobanteController::class, 'download'])
        ->name('comprobantes.descargar');
});
