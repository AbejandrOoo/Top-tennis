<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCanchaController extends Controller
{
    public function index()
    {
        $canchas = Cancha::all();
        return view('admin.canchas.index', compact('canchas'));
    }

    public function create()
    {
        return view('admin.canchas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'superficie' => 'required|string',
            'tipo_partido' => 'required|string',
            'iluminacion' => 'required|string',
            'estado' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'descripcion' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('canchas', 'public');
        }

        Cancha::create($data);

        return redirect()->route('admin.canchas.index')->with('success', 'Cancha registrada correctamente.');
    }

    public function edit($id)
    {
        $cancha = Cancha::findOrFail($id);
        return view('admin.canchas.edit', compact('cancha'));
    }

    public function update(Request $request, $id)
    {
        $cancha = Cancha::findOrFail($id);

        // Validaciones corregidas y suavizadas para que coincidan con los inputs de edit.blade.php
        $request->validate([
            'nombre' => 'required|string|max:255',
            'superficie' => 'required|string|max:255',
            'tipo_partido' => 'required|string|max:255',
            'iluminacion' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'descripcion' => 'nullable|string',
        ]);

        $data = $request->all();

        // Procesar la foto si se sube una nueva
        if ($request->hasFile('foto')) {
            if ($cancha->foto) {
                Storage::disk('public')->delete($cancha->foto);
            }
            $data['foto'] = $request->file('foto')->store('canchas', 'public');
        }

        // Guardar los cambios en la base de datos
        $cancha->update($data);

        // Redireccionar al listado principal con mensaje de éxito
        return redirect()->route('admin.canchas.index')->with('success', 'Cancha actualizada correctamente.');
    }

    public function deshabilitar($id)
    {
        $cancha = Cancha::findOrFail($id);
        $cancha->update(['estado' => 'En Mantenimiento']);

        return redirect()->route('admin.canchas.index')->with('success', 'La cancha ha sido deshabilitada correctamente.');
    }
}