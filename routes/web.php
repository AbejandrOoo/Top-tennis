<?php

// Aca traemos todos los controladores que vamos a necesitar para que las rutas sepan a donde ir cuando la gente hace clics por la pagina web
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

// Esta es la primera pantalla que ve cualquier persona apenas pone la direccion del local en su navegador web
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rutas del cliente que ya entro al sistema y puede manejar sus reservas
Route::middleware(['auth', 'verified'])->group(function () {
    // Aca es donde cae el usuario despues de poner su clave y entrar con exito a la plataforma
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Esto es para cuando quieren guardar una reserva nueva en la base de datos de nosotros
    Route::post('/reservas/store', [DashboardController::class, 'reservar'])->name('reservas.store');
    // Ruta que llama la gente cuando ya no va a poder ir a jugar y cancela su hora
    Route::post('/reservas/{id}/cancelar', [DashboardController::class, 'cancelar'])->name('reservas.cancelar');
    // Para mover la hora o el dia de una reserva si es que todavia se puede hacer un cambio de fecha
    Route::post('/reservas/{id}/reprogramar', [DashboardController::class, 'reprogramar'])->name('reservas.reprogramar');
    // Cuando deciden borrar la reserva por completo para que no quede registro visible en sus cuentas
    Route::delete('/reservas/{id}/eliminar', [DashboardController::class, 'eliminar'])->name('reservas.eliminar');
});

// Rutas del perfil que vienen con la base de autenticacion del proyecto
Route::middleware('auth')->group(function () {
    // La pagina donde cada quien mira sus datos personales y decide si cambia algo de su informacion
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Esto guarda los cambios que la persona hizo sobre su nombre o cosas asi en su perfil de usuario
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Una salida drastica por si alguien ya no quiere estar registrado en el sistema y se quiere ir de la base
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas del administrador para revisar caja y mantener datos principales
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->group(function () {
    // Esta pantalla es como el centro de control del dueño o encargado del negocio donde ve todo el panorama
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Seccion para crear y mantener las canchas que aparecen al cliente
    // Lista de todas las canchas que tenemos disponibles para mostrar
    Route::get('/canchas', [AdminCanchaController::class, 'index'])->name('admin.canchas.index');
    // Formulario vacio para cargar una nueva cancha al sistema general
    Route::get('/canchas/crear', [AdminCanchaController::class, 'create'])->name('admin.canchas.create');
    // Accion que agarra los datos del formulario de cancha y los mete a la tabla
    Route::post('/canchas/guardar', [AdminCanchaController::class, 'store'])->name('admin.canchas.store');
    // Pantalla para cambiarle el nombre o alguna cosa a una cancha que ya estaba creada
    Route::get('/canchas/{id}/editar', [AdminCanchaController::class, 'edit'])->name('admin.canchas.edit');
    
    // Se aceptan dos metodos porque algunos formularios pueden llegar distinto
    Route::match(['post', 'put'], '/canchas/{id}/actualizar', [AdminCanchaController::class, 'update'])->name('admin.canchas.update');
    
    // Sirve un monton por si la cancha esta en arreglo o le estan haciendo limpieza o pintura nueva
    Route::post('/canchas/{id}/deshabilitar', [AdminCanchaController::class, 'deshabilitar'])->name('admin.canchas.deshabilitar');

    // Para eliminar la cancha, si es que no tiene reservas
    Route::delete('/canchas/{id}', [AdminCanchaController::class, 'destroy'])->name('admin.canchas.destroy');

    // Acciones de caja para confirmar pagos y controlar el ingreso al local
    // Cuando el administrador revisa y da el visto bueno a una reserva pendiente de alguien
    Route::post('/reservas/{id}/aprobar', [AdminController::class, 'aprobar'])->name('admin.reservas.aprobar');
    // En caso de que algo este mal y el admin decida que esa reserva no va para ningun lado
    Route::post('/reservas/{id}/rechazar', [AdminController::class, 'rechazar'])->name('admin.reservas.rechazar');
    // Justo para cuando el cliente llega a la puerta y le marcamos que ya esta adentro del lugar
    Route::post('/reservas/{id}/checkin', [AdminController::class, 'checkin'])->name('admin.reservas.checkin');

    // Tarifas solo necesita listar crear editar y borrar para este sistema
    // Se deja fuera la vista de detalle porque no se usa en la pantalla actual
    Route::resource('tarifas', TarifaController::class)->except(['show']);
});

// Rutas de login registro y recuperacion de cuenta
// Trae toda la logica que viene separada de las claves y accesos para no amontonar las cosas por aqui
require __DIR__.'/auth.php';
