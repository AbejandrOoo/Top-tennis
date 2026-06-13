<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Cancha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // El constructor quedó limpio sin el middleware antiguo que causaba el error 500

    public function index()
    {
        $hoy = Carbon::now()->format('Y-m-d');

        // 1. Panel de Pagos Pendientes (Las transferencias que acaban de llegar)
        $pendientes = Reserva::with(['user', 'cancha'])
            ->where('estado', 'Pendiente')
            ->where('metodo_pago', 'yape')
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Agenda de Hoy (Partidos programados para el día actual)
        $agendaHoy = Reserva::with(['user', 'cancha'])
            ->where('fecha', $hoy)
            ->whereIn('estado', ['Verificado', 'Pendiente'])
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return view('admin.dashboard', compact('pendientes', 'agendaHoy'));
    }

    public function aprobar($id)
    {
        $reserva = Reserva::findOrFail($id);
        
        $reserva->update([
            'estado' => 'Verificado',
            'monto_pagado' => $reserva->total // Confirmamos que entró el dinero completo
        ]);

        return redirect()->back()->with('success', '¡Pago aprobado! Se generó el ticket del cliente y la cancha está confirmada.');
    }

    public function rechazar(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);
        
        // Al rechazar, la cancha vuelve a estar libre inmediatamente
        $reserva->update([
            'estado' => 'Rechazado',
            'tipo_cancelacion' => 'admin'
        ]);

        return redirect()->back()->with('error', 'Reserva rechazada. La cancha ha sido liberada para otros usuarios.');
    }

    public function checkin($id)
    {
        $reserva = Reserva::findOrFail($id);
        
        // Marcar que los jugadores ya llegaron al local
        $reserva->update([
            'ingresado' => true,
            'estado' => 'Completado'
        ]);

        return redirect()->back()->with('success', '¡Check-in exitoso! Jugadores en la cancha.');
    }
}