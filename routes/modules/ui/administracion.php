<?php

use App\Livewire\Centros\Index as CentrosIndex;
use App\Livewire\Conceptos\Index as ConceptosIndex;
use App\Livewire\Empleados\Index as EmpleadosIndex;
use App\Livewire\Politicas\Index as PoliticasIndex;
use App\Livewire\Proyectos\Index as ProyectosIndex;

Route::prefix('admin')->group(function () {

    Route::get('/empleados', EmpleadosIndex::class)
        ->name('empleados')
        ->middleware('permission:empleados.ver');

    Route::get('/proyectos', ProyectosIndex::class)
        ->name('proyectos')
        ->middleware('permission:proyectos.ver');

    Route::get('/conceptos', ConceptosIndex::class)
        ->name('conceptos')
        ->middleware('permission:conceptos.ver');

    Route::get('/politicas', PoliticasIndex::class)
        ->name('politicas')
        ->middleware('permission:politicas.ver');

    Route::get('/centros-costos', CentrosIndex::class)
        ->name('centros-costos')
        ->middleware('permission:centros_costos.ver');
});
