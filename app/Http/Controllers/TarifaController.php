<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarifa;
use App\Models\Cancha;

class TarifaController extends Controller
{
    public function index()
    {
        // Cargamos todas las tarifas ordenadas por cancha y luego por hora para que sea fácil de leer en tu panel
        $tarifas = Tarifa::with('cancha')->orderBy('cancha_id')->orderBy('hora_inicio')->get();
        $canchas = Cancha::all();
        
        return view('admin.tarifas.index', compact('tarifas', 'canchas'));
    }

    public function store(Request $request)
    {
        // 1. Validamos los datos básicos y que la hora de inicio sea MENOR que la hora de fin (after:hora_inicio)
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            'precio_hora' => 'required|numeric|min:0',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        // 2. Validación de solapamiento (Que no choque con otra tarifa activa de la misma cancha)
        $solapamiento = Tarifa::where('cancha_id', $request->cancha_id)
            ->where('estado', 'Activa')
            ->where(function ($query) use ($request) {
                $query->where('hora_inicio', '<', $request->hora_fin)
                      ->where('hora_fin', '>', $request->hora_inicio);
            })
            ->exists();

        if ($solapamiento) {
            return redirect()->back()->with('error', 'Error: El horario choca con otra tarifa activa para esta cancha.');
        }

        // 3. Si todo está bien, creamos la tarifa
        Tarifa::create([
            'cancha_id' => $request->cancha_id,
            'precio_hora' => $request->precio_hora,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'estado' => 'Activa' // Por defecto nace activa
        ]);

        return redirect()->back()->with('success', 'Tarifa registrada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        // 1. Validamos los datos de entrada
        $request->validate([
            'precio_hora' => 'required|numeric|min:0',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'estado' => 'required|in:Activa,Inactiva'
        ]);

        $tarifa = Tarifa::findOrFail($id);

        // 2. Si la tarifa se va a mantener o cambiar a 'Activa', revisamos que el nuevo horario no choque con otras
        if ($request->estado === 'Activa') {
            $solapamiento = Tarifa::where('cancha_id', $tarifa->cancha_id)
                ->where('id', '!=', $id) // Excluimos la tarifa actual de la búsqueda
                ->where('estado', 'Activa')
                ->where(function ($query) use ($request) {
                    $query->where('hora_inicio', '<', $request->hora_fin)
                          ->where('hora_fin', '>', $request->hora_inicio);
                })
                ->exists();

            if ($solapamiento) {
                return redirect()->back()->with('error', 'Error: El nuevo horario choca con otra tarifa activa.');
            }
        }

        // 3. Actualizamos los datos
        $tarifa->update([
            'precio_hora' => $request->precio_hora,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'estado' => $request->estado
        ]);

        return redirect()->back()->with('success', 'Tarifa actualizada correctamente.');
    }

    public function destroy($id)
    {
        $tarifa = Tarifa::findOrFail($id);
        
        // REQUERIMIENTO: Las tarifas no deben eliminarse sino manejarse con estado.
        // Hacemos una "eliminación lógica" pasándola a Inactiva para no romper reservas pasadas.
        $tarifa->update(['estado' => 'Inactiva']);

        return redirect()->back()->with('success', 'Tarifa desactivada. (El historial se mantiene intacto).');
    }
}