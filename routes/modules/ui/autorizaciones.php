<?php

use App\Livewire\Autorizaciones\Index;
use App\Livewire\Autorizaciones\Show;
use Illuminate\Support\Facades\Route;

Route::prefix('autorizaciones')->name('autorizaciones.')->group(function () {

    Route::get('/', Index::class)
        ->name('index')
        ->middleware('permission:solicitudes.aprobar');

    Route::get('/{solicitud}', Show::class)
        ->name('show')
        ->middleware('permission:solicitudes.aprobar');
});
