<?php

use App\Livewire\Presupuestos\Index;
use Illuminate\Support\Facades\Route;

Route::prefix('presupuestos')->name('presupuestos.')->group(function () {
    Route::get('/', Index::class)
        ->name('index')
        ->middleware('permission:presupuestos.ver');
});
