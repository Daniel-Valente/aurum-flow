<?php

use App\Livewire\Centros\Index as CentrosIndex;
use App\Livewire\Conceptos\Index as ConceptosIndex;
use App\Livewire\Empleados\Index as EmpleadosIndex;
use App\Livewire\Politicas\Index as PoliticasIndex;
use App\Livewire\Proyectos\Index as ProyectosIndex;
use App\Livewire\Areas\Index as AreasIndex;
use App\Livewire\Roles\Index as RolePermissionIndex;

Route::prefix('admin')->group(function () {

    Route::get('/empleados', EmpleadosIndex::class)
        ->name('empleados')
        ->middleware('permission:empleados.ver.propios|empleados.ver.area|empleados.ver.todos');

    Route::get('/proyectos', ProyectosIndex::class)
        ->name('proyectos')
        ->middleware('permission:proyectos.ver');

    Route::get('/conceptos', ConceptosIndex::class)
        ->name('conceptos')
        ->middleware('permission:conceptos.ver');

    Route::get('/politicas', PoliticasIndex::class)
        ->name('politicas')
        ->middleware('permission:politicas.ver');

    Route::get('/referencia-contable', CentrosIndex::class)
        ->name('centros-costos')
        ->middleware('permission:referencia_contable.ver');

    Route::get('/roles-permisos', RolePermissionIndex::class)
        ->name('roles-permisos')
        ->middleware('permission:roles.ver');

    Route::get('/areas', AreasIndex::class)->name('areas')->middleware('permission:areas.ver');
});
