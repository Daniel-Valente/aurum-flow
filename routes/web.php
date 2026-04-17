<?php

use App\Http\Controllers\Admin\Empleado\EmpleadoController;
use App\Http\Controllers\Admin\UserController;
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
    Route::prefix('admin/empleados')->group(function() {
        Route::get('/', [EmpleadoController::class, 'index'])
            ->name('empleados.index')
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
});

require __DIR__.'/settings.php';
