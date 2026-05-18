<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified', 'force.password', 'blocked'])->group(function () {
    //UI
    require __DIR__.'/modules/ui/dashboard.php';
    require __DIR__.'/modules/ui/administracion.php';
    require __DIR__.'/modules/ui/autorizaciones.php';
    require __DIR__.'/modules/ui/auditoria.php';
    require __DIR__.'/modules/ui/gastos.php';
    require __DIR__.'/modules/ui/tarjeta.php';
    require __DIR__.'/modules/ui/reportes.php';
    require __DIR__.'/modules/ui/solicitudes.php';
    require __DIR__.'/modules/ui/empresas.php';
    require __DIR__.'/modules/ui/presupuestos.php';

    //API
    require __DIR__.'/modules/api/solicitudes.php';
    require __DIR__.'/modules/api/gastos.php';
    require __DIR__.'/modules/api/excepciones.php';
});

require __DIR__ . '/settings.php';
