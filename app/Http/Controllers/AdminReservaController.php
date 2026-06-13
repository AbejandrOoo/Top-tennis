<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use Illuminate\Support\Str;

class AdminReservaController extends Controller
{
    /**
     * Muestra el panel del administrador con las reservas pendientes.
     */
    public function index()
    {
        // Traemos las reservas ordenadas por las más recientes
        $reservas = Reserva::with(['user', 'cancha'])->orderBy('id', 'desc')->get();
        return view('admin.reservas.index', compact('reservas'));
    }

    /**
     * Aprueba la reserva de Yape y genera el token único para el QR.
     */
    public function aprobar($id)
    {
        $reserva = Reserva::findOrFail($id);
        
        // Generamos un código único de acceso difícil de adivinar
        $codigoUnico = 'TT-' . strtoupper(Str::random(6)) . '-' . $reserva->id;

        $reserva->update([
            'estado' => 'Aprobada',
            'codigo_acceso' => $codigoUnico
        ]);

        return redirect()->back()->with('success', 'Reserva aprobada con éxito. Código QR generado para el cliente.');
    }

    /**
     * Vista de la cámara de escaneo para el Recepcionista.
     */
    public function escaner()
    {
        return view('admin.reservas.escaner');
    }

    /**
     * API que procesa el QR escaneado en milisegundos.
     */
    public function verificarQr(Request $request)
    {
        $request->validate(['codigo' => 'required|string']);

        $reserva = Reserva::where('codigo_acceso', $request->codigo)->first();

        if (!$reserva) {
            return response()->json(['status' => 'error', 'message' => 'Código QR inválido o falso.'], 404);
        }

        if ($reserva->estado !== 'Aprobada') {
            return response()->json(['status' => 'error', 'message' => 'Esta reserva no está aprobada o fue cancelada.'], 400);
        }

        if ($reserva->ingresado) {
            return response()->json(['status' => 'error', 'message' => '¡ALERTA! Este QR ya fue escaneado y el cliente ya ingresó anteriormente.'], 400);
        }

        // Validar si es el día correcto (Opcional pero recomendado en producción)
        if ($reserva->fecha !== date('Y-m-d')) {
            return response()->json(['status' => 'error', 'message' => "Esta reserva es para la fecha: {$reserva->fecha}, no para hoy."], 400);
        }

        // Si todo está perfecto, marcamos el ingreso
        $reserva->update(['ingresado' => true]);

        return response()->json([
            'status' => 'success',
            'message' => '¡ACCESO PERMITIDO!',
            'usuario' => $reserva->user->name,
            'cancha' => $reserva->cancha->nombre,
            'hora' => $reserva->hora_inicio
        ]);
    }
}