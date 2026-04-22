<?php

use App\Livewire\Reportes\Index;

Route::prefix('finanzas')->name('reportes.')->group(function () {
    Route::get('/reportes', Index::class)
        ->name('index')
        ->middleware('permission:gastos.ver.todos');
});
