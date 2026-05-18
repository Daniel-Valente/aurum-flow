<?php

use App\Livewire\Empresas\Index;
use Illuminate\Support\Facades\Route;

Route::prefix('empresas')->name('empresas.')->group(function () {
    Route::get('/', Index::class)
        ->name('index')
        ->middleware('permission:empresas.ver');

});
