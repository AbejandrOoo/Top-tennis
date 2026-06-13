<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCanchaController extends Controller
{
    public function index()
    {
        // Lista todas las canchas para que el administrador las revise
        // Desde esta vista se puede entrar a crear editar o deshabilitar
        $canchas = Cancha::all();
        return view('admin.canchas.index', compact('canchas'));
    }

    public function create()
    {
        // Muestra el formulario vacio para registrar una cancha nueva
        // La vista se mantiene separada del guardado para ordenar el flujo
        return view('admin.canchas.create');
    }

    public function store(Request $request)
    {
        // Se revisan los datos basicos antes de guardar la cancha
        // La foto es opcional pero si llega debe ser una imagen valida
        $request->validate([
            'nombre' => 'required|string|max:255',
            'superficie' => 'required|string',
            'tipo_partido' => 'required|string',
            'iluminacion' => 'required|string',
            'estado' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'descripcion' => 'nullable|string',
        ]);

        // Se toman los datos del formulario y luego se agrega la foto si existe
        // Asi el mismo arreglo sirve para crear el registro completo
        $data = $request->all();

        if ($request->hasFile('foto')) {
            // La imagen se guarda en el disco publico para poder mostrarla despues
            $data['foto'] = $request->file('foto')->store('canchas', 'public');
        }

        Cancha::create($data);

        return redirect()->route('admin.canchas.index')->with('success', 'Cancha registrada correctamente.');
    }

    public function edit($id)
    {
        // Se busca la cancha que se quiere modificar
        // Si no existe Laravel corta el flujo con una pagina de error normal
        $cancha = Cancha::findOrFail($id);
        return view('admin.canchas.edit', compact('cancha'));
    }

    public function update(Request $request, $id)
    {
        $cancha = Cancha::findOrFail($id);

        // Estas reglas coinciden con los campos que se editan en la pantalla
        // Se evita pedir datos que el formulario no esta enviando
        $request->validate([
            'nombre' => 'required|string|max:255',
            'superficie' => 'required|string|max:255',
            'tipo_partido' => 'required|string|max:255',
            'iluminacion' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'descripcion' => 'nullable|string',
        ]);

        // Se prepara la informacion nueva antes de actualizar el registro
        // Si no llega foto se conserva la que ya tenia la cancha
        $data = $request->all();

        if ($request->hasFile('foto')) {
            // Cuando llega una nueva foto se borra la anterior para no acumular archivos
            if ($cancha->foto) {
                Storage::disk('public')->delete($cancha->foto);
            }
            $data['foto'] = $request->file('foto')->store('canchas', 'public');
        }

        // Se guardan los cambios ya revisados en la base de datos
        $cancha->update($data);

        // Al terminar se vuelve al listado para confirmar que el cambio quedo
        return redirect()->route('admin.canchas.index')->with('success', 'Cancha actualizada correctamente.');
    }

    public function deshabilitar($id)
    {
        $cancha = Cancha::findOrFail($id);

        if ($cancha->estado === 'Disponible') {
            // Revisar si hay reservas activas a futuro
            $reservasActivas = \App\Models\Reserva::where('cancha_id', $cancha->id)
                ->whereIn('estado', ['Pendiente', 'Verificado'])
                ->where(function($q) {
                    $q->where('fecha', '>', now()->toDateString())
                      ->orWhere(function($subQ) {
                          $subQ->where('fecha', now()->toDateString())
                               ->where('hora_inicio', '>=', now()->toTimeString());
                      });
                })
                ->exists();

            if ($reservasActivas) {
                return redirect()->route('admin.canchas.index')->with('error', 'No se puede poner la cancha en mantenimiento porque tiene reservas activas pendientes.');
            }

            $cancha->update(['estado' => 'En Mantenimiento']);
            return redirect()->route('admin.canchas.index')->with('success', 'La cancha ha sido deshabilitada (En Mantenimiento) correctamente.');
        } else {
            $cancha->update(['estado' => 'Disponible']);
            return redirect()->route('admin.canchas.index')->with('success', 'La cancha ha sido habilitada correctamente.');
        }
    }

    public function destroy($id)
    {
        $cancha = Cancha::findOrFail($id);

        // Se verifica si hay reservas asociadas a esta cancha
        $tieneReservas = \App\Models\Reserva::where('cancha_id', $cancha->id)->exists();

        if ($tieneReservas) {
            return redirect()->route('admin.canchas.index')->with('error', 'No se puede eliminar la cancha porque tiene reservas asociadas.');
        }

        // Si hay una foto, la eliminamos también
        if ($cancha->foto) {
            Storage::disk('public')->delete($cancha->foto);
        }

        $cancha->delete();

        return redirect()->route('admin.canchas.index')->with('success', 'La cancha ha sido eliminada correctamente.');
    }
}
