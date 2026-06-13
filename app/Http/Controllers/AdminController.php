<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Cancha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $hoy = Carbon::now()->format('Y-m-d');

        // En esta parte se juntan los pagos que todavia esperan revision
        // El administrador los ve primero porque son los que necesitan respuesta
        $pendientes = Reserva::with(['user', 'cancha'])
            ->where('estado', 'Pendiente')
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
        
        // Se genera un código de acceso único para el ticket del cliente
        do {
            $codigo_acceso = 'TT' . Str::upper(Str::random(12));
        } while (Reserva::where('codigo_acceso', $codigo_acceso)->exists());

        $reserva->update([
            'estado' => 'Verificado',
            'monto_pagado' => $reserva->total,
            'codigo_acceso' => $codigo_acceso,
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

    public function updateYapeQr(Request $request)
    {
        $request->validate([
            'qr_yape' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('qr_yape')) {
            $request->file('qr_yape')->storeAs('/', 'yape_qr.png', 'public');
        }

        return redirect()->back()->with('success', 'Código QR de Yape actualizado correctamente.');
    }

    public function showScanForm()
    {
        $reserva = null;
        if (session('last_reserva_id')) {
            $reserva = Reserva::with(['user', 'cancha'])->find(session('last_reserva_id'));
        }
        return view('admin.reservas.scan', ['last_reserva' => $reserva]);
    }

    public function verifyQrCode(Request $request)
    {
        $request->validate(['codigo_acceso' => 'required|string|max:255']);

        $codigo = trim($request->input('codigo_acceso'));
        $reserva = Reserva::with(['user', 'cancha'])->where('codigo_acceso', $codigo)->first();

        if (!$reserva) {
            return redirect()->route('admin.reservas.showscan')->with('error', 'El código de acceso no es válido. No se encontró ninguna reserva.');
        }

        // Pasamos el ID de la reserva a la sesión en lugar del objeto completo
        if ($reserva->estado !== 'Verificado') {
            return redirect()->route('admin.reservas.showscan')->with([
                'error' => 'Esta reserva no está en un estado válido para el check-in. Su estado actual es: ' . $reserva->estado,
                'last_reserva_id' => $reserva->id
            ]);
        }

        if ($reserva->ingresado) {
            return redirect()->route('admin.reservas.showscan')->with([
                'success' => 'Este jugador ya había sido marcado como ingresado.',
                'last_reserva_id' => $reserva->id
            ]);
        }
        
        if ($reserva->fecha !== Carbon::now()->format('Y-m-d')) {
             return redirect()->route('admin.reservas.showscan')->with([
                'error' => 'El check-in solo se puede hacer el mismo día de la reserva.',
                'last_reserva_id' => $reserva->id
            ]);
        }

        $reserva->update([
            'ingresado' => true,
            'estado' => 'Completado'
        ]);

        return redirect()->route('admin.reservas.showscan')->with([
            'success' => '¡Check-in exitoso! Jugadores en la cancha.',
            'last_reserva_id' => $reserva->id
        ]);
    }
}
