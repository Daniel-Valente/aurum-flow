<?php

use App\Http\Controllers\Admin\Area\AreaController;
use App\Http\Controllers\Admin\CentroCosto\CentroCostoController;
use App\Http\Controllers\Admin\Concepto\ConceptoController;
use App\Http\Controllers\Admin\Empleado\EmpleadoController;
use App\Http\Controllers\Admin\PoliticaGasto\PoliticaGastoController;
use App\Http\Controllers\Admin\Proyecto\ProyectoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\GastoExcepcionController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified', 'force.password'])->group(function () {

    Route::view('dashboard', 'dashboard')
        ->name('dashboard');

    Route::get('/cambiar-password', function () {
        return view('auth.change-password');
    })->name('password.change');

    Route::post('/cambiar-password', function (Request $request) {
        $request->validate([
            'password' => 'required|min:8|confirmed'
        ]);

        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | EMPLEADOS MODULE (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/empleados')->group(function () {
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

    /*
    |--------------------------------------------------------------------------
    | AREA MODULE (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/areas')->group(function () {

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

    /*
    |--------------------------------------------------------------------------
    | CENTROS COSTOS MODULE (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/centros-costos')->group(function () {

        Route::get('/', [CentroCostoController::class, 'index'])
            ->middleware('permission:centros_costos.ver');

        Route::get('/list', [CentroCostoController::class, 'list'])
            ->middleware('permission:centros_costos.ver');

        Route::post('/', [CentroCostoController::class, 'store'])
            ->middleware('permission:centros_costos.crear');

        Route::put('/{centro_costo}', [CentroCostoController::class, 'update'])
            ->middleware('permission:centros_costos.editar');

        Route::patch('/{centro_costo}/toggle', [CentroCostoController::class, 'toggleStatus'])
            ->middleware('permission:centros_costos.editar');

        Route::delete('/{centro_costo}', [CentroCostoController::class, 'destroy'])
            ->middleware('permission:centros_costos.eliminar');
    });

    /*
    |--------------------------------------------------------------------------
    | PROYECTOS MODULE (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/proyectos')->group(function () {

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

    /*
    |--------------------------------------------------------------------------
    | CONCEPTOS MODULE (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/conceptos')->group(function () {

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

    /*
    |--------------------------------------------------------------------------
    | POLITICAS MODULE (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/politicas')->group(function () {

        Route::get('/', [PoliticaGastoController::class, 'index'])
            ->middleware('permission:politicas.ver');

        Route::post('/', [PoliticaGastoController::class, 'store'])
            ->middleware('permission:politicas.crear');

        Route::delete('/{politica}', [PoliticaGastoController::class, 'destroy'])
            ->middleware('permission:politicas.eliminar');
    });

    Route::post('/gastos', [GastoController::class, 'store'])
        ->middleware('permission:gastos.crear');

    Route::post('/excepciones/{excepcion}/resolver', [GastoExcepcionController::class, 'resolver'])
        ->middleware('permission:excepciones.aprobar.nivel1|excepciones.aprobar.nivel2');

    Route::get('/excepciones', [GastoExcepcionController::class, 'index'])
        ->middleware('permission:excepciones.ver');

    Route::get('/excepciones/{excepcion}', [GastoExcepcionController::class, 'show'])
        ->middleware('permission:excepciones.ver');

    Route::get('/gastos/{gasto}/timeline', [GastoController::class, 'timeline'])
        ->middleware('permission:gastos.ver.todos');

    Route::prefix('solicitudes')->middleware('auth')->group(function () {

        Route::post('/', [SolicitudController::class, 'store'])
            ->middleware('permission:solicitudes.crear');

        Route::post('/{id}/detalle', [SolicitudController::class, 'agregarDetalle'])
            ->middleware('permission:solicitudes.editar');

        Route::post('/{id}/enviar', [SolicitudController::class, 'enviar'])
            ->middleware('permission:solicitudes.enviar');

        Route::post('/{id}/resolver', [SolicitudController::class, 'resolver'])
            ->middleware('permission:solicitudes.aprobar');

        Route::get('/solicitudes', [SolicitudController::class, 'index'])
            ->middleware('auth');

        Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])
            ->middleware('auth');

        Route::patch('/solicitudes/{solicitud}/cancelar', [SolicitudController::class, 'cancelar']);
        Route::patch('/solicitudes/{solicitud}/reabrir', [SolicitudController::class, 'reabrir']);
        Route::patch('/solicitudes/{solicitud}/aprobar', [SolicitudController::class, 'aprobar']);
    });
});

require __DIR__ . '/settings.php';
