<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED AREA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |-----------------------------------------
    | DASHBOARD (todos los roles)
    |-----------------------------------------
    */
    Route::view('dashboard', 'dashboard')
        ->name('dashboard');

    /*
    |-----------------------------------------
    | OPERATIVO
    |-----------------------------------------
    */
    /*Route::middleware(['role:operativo'])->group(function () {
        Route::view('mis-solicitudes', 'solicitudes.index');
        Route::view('gastos', 'gastos.index');
    });*/

    /*
    |-----------------------------------------
    | GERENTE
    |-----------------------------------------
    */
    /*Route::middleware(['role:gerente'])->group(function () {
        Route::view('autorizaciones', 'autorizaciones.index');
        Route::view('excepciones', 'excepciones.index');
    });*/

    /*
    |-----------------------------------------
    | ADMIN
    |-----------------------------------------
    */
    /*Route::middleware(['role:admin'])->group(function () {
        Route::view('empleados', 'empleados.index');
        Route::view('politicas', 'politicas.index');
        Route::view('reportes', 'reportes.index');
    });*/

});

require __DIR__.'/settings.php';
