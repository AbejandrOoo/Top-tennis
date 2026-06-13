<?php

namespace App\Http\Controllers;

use App\Models\Tarifa;
use App\Models\Cancha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TarifaController extends Controller
{
    // Muestra las tarifas junto con la cancha para que el administrador las revise
    // Es la pantalla principal de este mantenimiento
    public function index()
    {
        // Se carga la cancha relacionada para no mostrar solo codigos internos
        // Asi la tabla queda mas clara al momento de editar precios
        $tarifas = Tarifa::with('cancha')->get();
        return view('tarifas.index', compact('tarifas'));
    }

    // Abre el formulario donde se registra un nuevo precio por turno
    // Se necesitan las canchas para armar el selector
    public function create()
    {
        // Se listan todas las canchas disponibles para asignar la tarifa
        // El administrador decide a cual cancha pertenece el precio
        $canchas = Cancha::all();
        return view('tarifas.create', compact('canchas'));
    }

    // Guarda una tarifa nueva despues de revisar los datos del formulario
    // La combinacion de cancha y turno no debe repetirse
    public function store(Request $request)
    {
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            // Una cancha solo debe tener una tarifa activa por cada turno
            // Esto evita dudas al calcular el total de una reserva
            'turno' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tarifas')->where(fn ($query) => $query
                    ->where('cancha_id', $request->cancha_id)
                    ->whereNull('deleted_at')),
            ],
            'precio_hora' => 'required|numeric|min:0',
        ]);

        Tarifa::create($request->all());

        return redirect()->route('tarifas.index')->with('success', '¡Tarifa registrada con éxito!');
    }

    // Abre el formulario con los datos actuales de la tarifa
    // Desde aqui se puede cambiar cancha turno o precio
    public function edit(Tarifa $tarifa)
    {
        $canchas = Cancha::all();
        return view('tarifas.edit', compact('tarifa', 'canchas'));
    }

    // Actualiza una tarifa existente con las mismas reglas que al crear
    // Se permite guardar la misma tarifa sin tomarla como duplicada
    public function update(Request $request, Tarifa $tarifa)
    {
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            // Se ignora la misma tarifa para poder guardar cambios normales
            // La validacion solo bloquea duplicados reales de otra fila
            'turno' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tarifas')
                    ->where(fn ($query) => $query
                        ->where('cancha_id', $request->cancha_id)
                        ->whereNull('deleted_at'))
                    ->ignore($tarifa->id),
            ],
            'precio_hora' => 'required|numeric|min:0',
        ]);

        $tarifa->update($request->all());

        return redirect()->route('tarifas.index')->with('success', '¡Tarifa actualizada!');
    }

    // Elimina la tarifa de forma logica para conservar el historial basico
    // Si luego se necesita otra tarifa del mismo turno se podra crear de nuevo
    public function destroy(Tarifa $tarifa)
    {
        $tarifa->delete();
        return redirect()->route('tarifas.index')->with('success', '¡Tarifa eliminada del sistema!');
    }
}
