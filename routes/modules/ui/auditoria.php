<?php

use App\Livewire\Auditoria\Index;
use App\Livewire\Auditoria\Show;
use Illuminate\Support\Facades\Route;

Route::prefix('auditoria')->name('auditoria.')->group(function () {
    Route::get('/', Index::class)
        ->name('index')
        ->middleware('permission:gastos.ver.todos');

    Route::get('/{proyecto}', Show::class)
        ->name('show')
        ->middleware('permission:gastos.ver.todos');
});
