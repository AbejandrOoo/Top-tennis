<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mantenimiento;
use App\Models\Cancha;
use App\Models\Reserva;
use Carbon\Carbon;

class AdminMantenimientoController extends Controller
{
    public function index()
    {
        $mantenimientos = Mantenimiento::with('cancha')->orderBy('fecha_inicio', 'desc')->get();
        $canchas = Cancha::all();
        
        return view('admin.mantenimientos.index', compact('mantenimientos', 'canchas'));
    }

    public function store(Request $request)
    {
        // 1. Validamos que la fecha final sea DESPUÉS de la inicial
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'motivo' => 'required|string|max:255',
        ]);

        // 2. Verificamos que no haya otro mantenimiento activo en ese mismo rango
        $solapamiento = Mantenimiento::where('cancha_id', $request->cancha_id)
            ->whereIn('estado', ['Programado', 'En proceso'])
            ->where(function ($query) use ($request) {
                $query->where('fecha_inicio', '<', $request->fecha_fin)
                      ->where('fecha_fin', '>', $request->fecha_inicio);
            })
            ->exists();

        if ($solapamiento) {
            return redirect()->back()->with('error', 'Error: La cancha ya tiene otro mantenimiento programado que cruza con estas fechas.');
        }

        // 3. Creamos el mantenimiento
        $mantenimiento = Mantenimiento::create([
            'cancha_id' => $request->cancha_id,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'motivo' => $request->motivo,
            'estado' => 'Programado' // Por defecto nace como Programado
        ]);

        // 4. Magia automática: Cancelar reservas que ya existían en esas fechas
        $canceladas = $this->cancelarReservasAfectadas($mantenimiento);

        $mensaje = 'Mantenimiento registrado correctamente.';
        if ($canceladas > 0) {
            $mensaje .= " Se han cancelado automáticamente $canceladas reserva(s) que chocaban con este horario.";
        }

        return redirect()->back()->with('success', $mensaje);
    }

    public function update(Request $request, $id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'motivo' => 'required|string|max:255',
            'estado' => 'required|in:Programado,En proceso,Finalizado,Cancelado',
        ]);

        // Solo validamos solapamientos si el mantenimiento está activo
        if (in_array($request->estado, ['Programado', 'En proceso'])) {
            $solapamiento = Mantenimiento::where('cancha_id', $mantenimiento->cancha_id)
                ->where('id', '!=', $id) // Excluimos este mismo registro
                ->whereIn('estado', ['Programado', 'En proceso'])
                ->where(function ($query) use ($request) {
                    $query->where('fecha_inicio', '<', $request->fecha_fin)
                          ->where('fecha_fin', '>', $request->fecha_inicio);
                })
                ->exists();

            if ($solapamiento) {
                return redirect()->back()->with('error', 'Error: Las nuevas fechas chocan con otro mantenimiento activo.');
            }
        }

        $mantenimiento->update($request->only(['fecha_inicio', 'fecha_fin', 'motivo', 'estado']));

        // Si se reprogramó a nuevas fechas, volvemos a barrer las reservas por si hay nuevos choques
        if (in_array($mantenimiento->estado, ['Programado', 'En proceso'])) {
            $this->cancelarReservasAfectadas($mantenimiento);
        }

        return redirect()->back()->with('success', 'Mantenimiento actualizado.');
    }

    public function destroy($id)
    {
        // REQUERIMIENTO: Los mantenimientos no deben eliminarse sino manejarse mediante estados.
        $mantenimiento = Mantenimiento::findOrFail($id);
        $mantenimiento->update(['estado' => 'Cancelado']);

        return redirect()->back()->with('success', 'Mantenimiento cancelado. (Historial conservado).');
    }

    /**
     * Función privada que busca y cancela reservas cruzadas
     */
    private function cancelarReservasAfectadas(Mantenimiento $mantenimiento)
    {
        // Buscamos todas las reservas activas
        $reservas = Reserva::where('cancha_id', $mantenimiento->cancha_id)
            ->whereIn('estado', ['Pendiente', 'Aprobada']) 
            ->get();

        $contadorCanceladas = 0;

        foreach ($reservas as $reserva) {
            // Usamos tu columna fecha y las juntamos con hora_inicio y hora_fin
            $inicioReserva = Carbon::parse($reserva->fecha . ' ' . $reserva->hora_inicio);
            $finReserva = Carbon::parse($reserva->fecha . ' ' . $reserva->hora_fin);

            // Matemáticas de solapamiento
            if ($inicioReserva < $mantenimiento->fecha_fin && $finReserva > $mantenimiento->fecha_inicio) {
                // ¡Choque detectado! Cancelamos la reserva y usamos tu columna tipo_cancelacion
                $reserva->update([
                    'estado' => 'Cancelada',
                    'tipo_cancelacion' => 'Por Mantenimiento' // 👈 Aprovechamos tu columna
                ]);
                $contadorCanceladas++;
            }
        }

        return $contadorCanceladas;
    }
}