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

// Rutas del cliente que ya entro al sistema y puede manejar sus reservas
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/reservas/store', [DashboardController::class, 'reservar'])->name('reservas.store');
    Route::post('/reservas/{id}/cancelar', [DashboardController::class, 'cancelar'])->name('reservas.cancelar');
    Route::post('/reservas/{id}/reprogramar', [DashboardController::class, 'reprogramar'])->name('reservas.reprogramar');
    Route::delete('/reservas/{id}/eliminar', [DashboardController::class, 'eliminar'])->name('reservas.eliminar');
});

// Rutas del perfil que vienen con la base de autenticacion del proyecto
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas del administrador para revisar caja y mantener datos principales
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Seccion para crear y mantener las canchas que aparecen al cliente
    Route::get('/canchas', [AdminCanchaController::class, 'index'])->name('admin.canchas.index');
    Route::get('/canchas/crear', [AdminCanchaController::class, 'create'])->name('admin.canchas.create');
    Route::post('/canchas/guardar', [AdminCanchaController::class, 'store'])->name('admin.canchas.store');
    Route::get('/canchas/{id}/editar', [AdminCanchaController::class, 'edit'])->name('admin.canchas.edit');
    
    // Se aceptan dos metodos porque algunos formularios pueden llegar distinto
    Route::match(['post', 'put'], '/canchas/{id}/actualizar', [AdminCanchaController::class, 'update'])->name('admin.canchas.update');
    
    Route::post('/canchas/{id}/deshabilitar', [AdminCanchaController::class, 'deshabilitar'])->name('admin.canchas.deshabilitar');

    // Acciones de caja para confirmar pagos y controlar el ingreso al local
    Route::post('/reservas/{id}/aprobar', [AdminController::class, 'aprobar'])->name('admin.reservas.aprobar');
    Route::post('/reservas/{id}/rechazar', [AdminController::class, 'rechazar'])->name('admin.reservas.rechazar');
    Route::post('/reservas/{id}/checkin', [AdminController::class, 'checkin'])->name('admin.reservas.checkin');

    // Tarifas solo necesita listar crear editar y borrar para este sistema
    // Se deja fuera la vista de detalle porque no se usa en la pantalla actual
    Route::resource('tarifas', TarifaController::class)->except(['show']);
});

// Rutas de login registro y recuperacion de cuenta
require __DIR__.'/auth.php';
