<?php

// Traemos todas las clases de seguridad que arman el rompecabezas para dejar que la gente entre al sistema de manera segura
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Este bloque gigante de aca sirve unicamente para las personas que todavia no tienen cuenta o que andan sin entrar todavia
Route::middleware('guest')->group(function () {
    // La pantalla basica donde alguien que recien nos conoce pone sus datos por primera vez para hacerse usuario
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    // Aca es donde agarramos toda la informacion de registro y la metemos a la base de datos para crear la cuenta
    Route::post('register', [RegisteredUserController::class, 'store']);

    // La clasica ventana para poner el correo y la clave cuando alguien ya es cliente nuestro
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // Comprobamos si las credenciales que pusieron son validas y si es asi los dejamos entrar al sistema
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Por si algun usuario se olvida de su clave y necesita que le mandemos un aviso para recuperarla
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    // Esta parte se encarga de procesar el envio del correo de recuperacion cuando nos lo piden
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // El enlace especial que llega al correo para que la persona pueda escribir su nueva clave secreta
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    // Guardamos la nueva clave que nos pasaron para reemplazar la anterior que se habia perdido
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// Todas estas rutas de aca abajo son exclusivas para gente que ya metio su clave y esta navegando por adentro de la pagina
Route::middleware('auth')->group(function () {
    // Les mostramos un cartelito pidiendo que por favor revisen su correo para confirmar que es de ellos
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // Esta ruta rara es a donde llegan cuando hacen clic en el enlace de validacion que les mandamos al mail
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Por si acaso no les llego el primer correo aca pueden pedir que les mandemos otro de verificacion
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // A veces pedimos que pongan la clave de nuevo antes de hacer cosas importantes como medida extra de seguridad
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    // Revisamos que la clave que acaban de poner para confirmar que son ellos mismos este correcta
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Funcionalidad para que cualquier usuario pueda entrar a su perfil y cambiar su clave por otra cuando quiera
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // La accion de cerrar la sesion actual para que nadie mas pueda usar su cuenta en esa computadora
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});