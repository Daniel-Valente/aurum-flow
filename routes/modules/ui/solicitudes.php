<?php

use App\Livewire\Solicitudes\Index;
use App\Livewire\Solicitudes\Show;
use Illuminate\Support\Facades\Route;

Route::prefix('mis-solicitudes')->name('solicitudes.')->group(function () {
    Route::get('/', Index::class)
        ->name('index')
        ->middleware('permission:solicitudes.ver.propias|solicitudes.ver.todas');

    Route::get('/{solicitud}', Show::class)
        ->name('show')
        ->middleware('permission:solicitudes.ver.propias|solicitudes.ver.todas');
});
