<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cancha;

class AdminCanchaController extends Controller
{
    // Listar todas las canchas (READ)
    public function index()
    {
        $canchas = Cancha::all();
        return view('admin.canchas.index', compact('canchas'));
    }

    // Mostrar formulario de creación (CREATE)
    public function create()
    {
        return view('admin.canchas.create');
    }

    // Guardar la nueva cancha en la BD
    public function store(Request $request)
    {
        // Validamos usando el nombre exacto de tu columna en la BD
        $request->validate([
            'nombre' => 'required|string|max:255|unique:canchas,nombre',
            'superficie' => 'required|string',
            'tiene_luz' => 'required|boolean',
        ]);

        Cancha::create([
            'nombre' => $request->nombre,
            'superficie' => $request->superficie,
            'tiene_luz' => $request->tiene_luz,
            'estado' => 'Disponible'
        ]);

        return redirect()->route('admin.canchas.index')->with('success', 'Cancha registrada exitosamente.');
    }

    // Mostrar formulario de edición (UPDATE)
    public function edit($id)
    {
        $cancha = Cancha::findOrFail($id);
        return view('admin.canchas.edit', compact('cancha'));
    }

    // Actualizar los datos en la BD
    public function update(Request $request, $id)
    {
        $cancha = Cancha::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:canchas,nombre,' . $cancha->id,
            'superficie' => 'required|string',
            'tiene_luz' => 'required|boolean',
            'estado' => 'required|string',
        ]);

        $cancha->update([
            'nombre' => $request->nombre,
            'superficie' => $request->superficie,
            'tiene_luz' => $request->tiene_luz,
            'estado' => $request->estado,
        ]);

        return redirect()->route('admin.canchas.index')->with('success', 'Cancha actualizada correctamente.');
    }

    // Deshabilitar de forma segura (Regla 3.3 de tu entrega)
    public function deshabilitar($id)
    {
        $cancha = Cancha::findOrFail($id);
        
        $nuevoEstado = ($cancha->estado === 'Disponible') ? 'Mantenimiento' : 'Disponible';
        $cancha->update(['estado' => $nuevoEstado]);

        return redirect()->route('admin.canchas.index')->with('success', 'El estado de la cancha ha sido modificado.');
    }
}