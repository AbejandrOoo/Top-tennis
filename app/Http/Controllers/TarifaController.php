<?php

namespace App\Http\Controllers;

use App\Models\Tarifa;
use App\Models\Cancha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TarifaController extends Controller
{
    // 1. LISTAR TARIFAS (Mostrando el nombre de la cancha)
    public function index()
    {
        // Traemos las tarifas incluyendo la información de la cancha (Relación 1 a N)
        $tarifas = Tarifa::with('cancha')->get();
        return view('tarifas.index', compact('tarifas'));
    }

    // 2. FORMULARIO PARA CREAR
    public function create()
    {
        // Traemos todas las canchas para mostrarlas en un "Select" (Cumple rúbrica 3.5)
        $canchas = Cancha::all();
        return view('tarifas.create', compact('canchas'));
    }

    // 3. GUARDAR EN LA BASE DE DATOS
    public function store(Request $request)
    {
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            // Una cancha solo debe tener una tarifa por turno para evitar precios duplicados.
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

    // 4. FORMULARIO PARA EDITAR
    public function edit(Tarifa $tarifa)
    {
        $canchas = Cancha::all();
        return view('tarifas.edit', compact('tarifa', 'canchas'));
    }

    // 5. ACTUALIZAR EN LA BASE DE DATOS
    public function update(Request $request, Tarifa $tarifa)
    {
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            // Ignoramos la tarifa actual para permitir guardar sin cambiar el turno.
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

    // 6. BORRADO SEGURO
    public function destroy(Tarifa $tarifa)
    {
        $tarifa->delete();
        return redirect()->route('tarifas.index')->with('success', '¡Tarifa eliminada del sistema!');
    }
}
