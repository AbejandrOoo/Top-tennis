<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Cancha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $hoy = Carbon::now()->format('Y-m-d');

        // En esta parte se juntan los pagos que todavia esperan revision
        // El administrador los ve primero porque son los que necesitan respuesta
        $pendientes = Reserva::with(['user', 'cancha'])
            ->where('estado', 'Pendiente')
            ->where('metodo_pago', 'yape')
            ->orderBy('created_at', 'asc')
            ->get();

        // Tambien se arma la agenda del dia para ver que partidos vienen
        // Aqui aparecen reservas pendientes y verificadas para control de caja
        $agendaHoy = Reserva::with(['user', 'cancha'])
            ->where('fecha', $hoy)
            ->whereIn('estado', ['Verificado', 'Pendiente'])
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return view('admin.dashboard', compact('pendientes', 'agendaHoy'));
    }

    public function aprobar($id)
    {
        // Cuando el pago se acepta la reserva queda confirmada para el cliente
        // Tambien se marca el monto como pagado para cuadrar caja
        $reserva = Reserva::findOrFail($id);
        
        $reserva->update([
            'estado' => 'Verificado',
            'monto_pagado' => $reserva->total
        ]);

        return redirect()->back()->with('success', '¡Pago aprobado! Se generó el ticket del cliente y la cancha está confirmada.');
    }

    public function rechazar(Request $request, $id)
    {
        // Si el pago no corresponde se libera la reserva para otros usuarios
        // El cambio de estado deja claro que fue una accion del administrador
        $reserva = Reserva::findOrFail($id);
        
        $reserva->update([
            'estado' => 'Rechazado',
            'tipo_cancelacion' => 'admin'
        ]);

        return redirect()->back()->with('error', 'Reserva rechazada. La cancha ha sido liberada para otros usuarios.');
    }

    public function checkin($id)
    {
        // Esta accion confirma que el jugador ya llego al local
        // Luego la reserva queda como completada para no seguir apareciendo igual
        $reserva = Reserva::findOrFail($id);
        
        $reserva->update([
            'ingresado' => true,
            'estado' => 'Completado'
        ]);

        return redirect()->back()->with('success', '¡Check-in exitoso! Jugadores en la cancha.');
    }
}
