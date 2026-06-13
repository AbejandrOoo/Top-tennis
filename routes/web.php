<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCanchaController; 
use App\Http\Controllers\TarifaController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// GRUPO DE RESERVAS PROTEGIDAS (USUARIOS CLIENTES)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/reservas/store', [DashboardController::class, 'reservar'])->name('reservas.store');
    Route::post('/reservas/{id}/cancelar', [DashboardController::class, 'cancelar'])->name('reservas.cancelar');
    Route::post('/reservas/{id}/reprogramar', [DashboardController::class, 'reprogramar'])->name('reservas.reprogramar');
    Route::delete('/reservas/{id}/eliminar', [DashboardController::class, 'eliminar'])->name('reservas.eliminar');
});

// PERFIL DE USUARIO NATIVO (BREEZE)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// RUTAS DEL ADMINISTRADOR (PANEL PRINCIPAL + CRUD CANCHAS Y TARIFAS)
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // CRUD CANCHAS
    Route::get('/canchas', [AdminCanchaController::class, 'index'])->name('admin.canchas.index');
    Route::get('/canchas/crear', [AdminCanchaController::class, 'create'])->name('admin.canchas.create');
    Route::post('/canchas/guardar', [AdminCanchaController::class, 'store'])->name('admin.canchas.store');
    Route::get('/canchas/{id}/editar', [AdminCanchaController::class, 'edit'])->name('admin.canchas.edit');
    
    // SOLUCIÓN: Match acepta tanto POST como PUT para evitar el bloqueo 405 por la caché
    Route::match(['post', 'put'], '/canchas/{id}/actualizar', [AdminCanchaController::class, 'update'])->name('admin.canchas.update');
    
    Route::post('/canchas/{id}/deshabilitar', [AdminCanchaController::class, 'deshabilitar'])->name('admin.canchas.deshabilitar');

    // Procesos de caja admin
    Route::post('/reservas/{id}/aprobar', [AdminController::class, 'aprobar'])->name('admin.reservas.aprobar');
    Route::post('/reservas/{id}/rechazar', [AdminController::class, 'rechazar'])->name('admin.reservas.rechazar');
    Route::post('/reservas/{id}/checkin', [AdminController::class, 'checkin'])->name('admin.reservas.checkin');

    // CRUD TARIFAS
    // Excluimos "show" porque el sistema solo lista, crea, edita y elimina tarifas.
    // Asi evitamos una ruta a un metodo que no existe en TarifaController.
    Route::resource('tarifas', TarifaController::class)->except(['show']);
});

// CARGA DE AUTENTICACIÓN
require __DIR__.'/auth.php';
