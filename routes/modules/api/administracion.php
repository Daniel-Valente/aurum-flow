<?php

use App\Http\Controllers\Admin\Area\AreaController;
use App\Http\Controllers\Admin\CentroCosto\CentroCostoController;
use App\Http\Controllers\Admin\Concepto\ConceptoController;
use App\Http\Controllers\Admin\Empleado\EmpleadoController;
use App\Http\Controllers\Admin\PoliticaGasto\PoliticaGastoController;
use App\Http\Controllers\Admin\Proyecto\ProyectoController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('empleados')->group(function () {
        Route::get('/', [EmpleadoController::class, 'index'])
            ->name('empleados.index')
            ->middleware('permission:empleados.ver');

        Route::get('/list', [EmpleadoController::class, 'list'])
            ->middleware('permission:empleados.ver');

        Route::post('/', [EmpleadoController::class, 'store'])
            ->name('empleados.store')
            ->middleware('permission:empleados.crear');

        Route::put('/{empleado}', [EmpleadoController::class, 'update'])
            ->name('empleados.update')
            ->middleware('permission:empleados.editar');

        Route::delete('/{empleado}', [EmpleadoController::class, 'destroy'])
            ->name('empleados.destroy')
            ->middleware('permission:empleados.eliminar');

        Route::patch('/{empleado}/toggle', [EmpleadoController::class, 'toggleStatus'])
            ->name('empleados.toggle')
            ->middleware('permission:empleados.bloquear');
    });

    Route::prefix('areas')->group(function () {
        Route::get('/', [AreaController::class, 'index'])
            ->middleware('permission:areas.ver');

        Route::get('/list', [AreaController::class, 'list'])
            ->middleware('permission:areas.ver');

        Route::post('/', [AreaController::class, 'store'])
            ->middleware('permission:areas.crear');

        Route::put('/{area}', [AreaController::class, 'update'])
            ->middleware('permission:areas.editar');

        Route::delete('/{area}', [AreaController::class, 'destroy'])
            ->middleware('permission:areas.eliminar');

        Route::patch('/{area}/toggle', [AreaController::class, 'toggle'])
            ->middleware('permission:areas.editar');
    });

    Route::prefix('centros-costos')->group(function () {
        Route::get('/', [CentroCostoController::class, 'index'])
            ->middleware('permission:referencia_contable.ver');

        Route::get('/list', [CentroCostoController::class, 'list'])
            ->middleware('permission:referencia_contable.ver');

        Route::post('/', [CentroCostoController::class, 'store'])
            ->middleware('permission:referencia_contable.crear');

        Route::put('/{centro_costo}', [CentroCostoController::class, 'update'])
            ->middleware('permission:referencia_contable.editar');

        Route::patch('/{centro_costo}/toggle', [CentroCostoController::class, 'toggleStatus'])
            ->middleware('permission:referencia_contable.editar');

        Route::delete('/{centro_costo}', [CentroCostoController::class, 'destroy'])
            ->middleware('permission:referencia_contable.eliminar');
    });

    Route::prefix('proyectos')->group(function () {
        Route::get('/', [ProyectoController::class, 'index'])
            ->middleware('permission:proyectos.ver');

        Route::get('/list', [ProyectoController::class, 'list'])
            ->middleware('permission:proyectos.ver');

        Route::post('/', [ProyectoController::class, 'store'])
            ->middleware('permission:proyectos.crear');

        Route::put('/{proyecto}', [ProyectoController::class, 'update'])
            ->middleware('permission:proyectos.editar');

        Route::patch('/{proyecto}/toggle', [ProyectoController::class, 'toggleStatus'])
            ->middleware('permission:proyectos.editar');

        Route::delete('/{proyecto}', [ProyectoController::class, 'destroy'])
            ->middleware('permission:proyectos.eliminar');
    });

    Route::prefix('conceptos')->group(function () {
        Route::get('/', [ConceptoController::class, 'index'])
            ->middleware('permission:conceptos.ver');

        Route::get('/list', [ConceptoController::class, 'list'])
            ->middleware('permission:conceptos.ver');

        Route::post('/', [ConceptoController::class, 'store'])
            ->middleware('permission:conceptos.crear');

        Route::put('/{concepto}', [ConceptoController::class, 'update'])
            ->middleware('permission:conceptos.editar');

        Route::delete('/{concepto}', [ConceptoController::class, 'destroy'])
            ->middleware('permission:conceptos.eliminar');

        Route::patch('/{concepto}/toggle', [ConceptoController::class, 'toggle'])
            ->middleware('permission:conceptos.editar');
    });

    Route::prefix('politicas')->group(function () {
        Route::get('/', [PoliticaGastoController::class, 'index'])
            ->middleware('permission:politicas.ver');

        Route::post('/', [PoliticaGastoController::class, 'store'])
            ->middleware('permission:politicas.crear');

        Route::put('/{politica}', [PoliticaGastoController::class, 'update']);

        Route::delete('/{politica}', [PoliticaGastoController::class, 'destroy'])
            ->middleware('permission:politicas.eliminar');

        Route::get('/{politica}/versiones', [PoliticaGastoController::class, 'versiones']);
        Route::get('/{politica}/auditoria', [PoliticaGastoController::class, 'auditoria']);
    });
});
