<?php

namespace App\Http\Controllers;

// Importamos un monton de clases y cosas que usa laravel por detras para hacer funcionar todo esto de las peticiones las respuestas y la seguridad del usuario
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // Aca es donde armamos y mandamos la pantalla para que la persona pueda revisar y cambiar sus datos personales cuando quiera
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    // Esta parte se encarga de recibir toda la informacion nueva que mandan desde el formulario del perfil y la mete a la base de datos de nosotros
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        // Si por alguna razon la persona decide cambiarse su correo electronico tenemos que borrar la confirmacion anterior para que valide su nueva direccion
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Finalmente guardamos todos los cambios hechos en la cuenta para que no se pierdan
        $request->user()->save();

        // Al terminar de guardar los devolvemos a la misma pagina con un aviso interno para saber que todo salio bien
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // Esta es una de las partes mas delicadas donde procedemos a eliminar por completo la cuenta del sistema sin dejar ningun rastro de ellos
    public function destroy(Request $request): RedirectResponse
    {
        // Primero le pedimos obligatoriamente que escriba su clave actual para estar completamente seguros de que no es un intruso intentando hacer dano
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Sacamos a la persona de la pagina web antes de borrar sus datos porque sino el sistema se vuelve loco
        Auth::logout();

        // Hacemos el borrado total y definitivo de su registro en nuestra tabla de usuarios
        $user->delete();

        // Limpiamos cualquier sesion o dato temporal que haya quedado guardado en el navegador web para cerrar el ciclo
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Despues de que su cuenta ya no existe mas los mandamos directo a la portada de inicio principal
        return Redirect::to('/');
    }
}
