<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cancha;
use App\Models\Reserva;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // La limpieza de reservas expiradas ahora la maneja el Comando Programado (Cron Job) en segundo plano.
        // Esto hace que el Dashboard cargue mucho más rápido.
        if (Auth::user()->rol === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
        $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));
        $horaInicioInput = $request->input('hora');
        $duracionInput = (int) $request->input('duracion', 1);

        if (!$horaInicioInput) {
            if ($fecha === Carbon::now()->format('Y-m-d')) {
                $horaSugerida = Carbon::now()->addHour()->hour;
                $horaInicioInput = ($horaSugerida >= 6 && $horaSugerida <= 22) ? sprintf('%02d:00', $horaSugerida) : '06:00';
            } else {
                $horaInicioInput = '06:00';
            }
        }

        $fechaReservaCompleta = Carbon::parse($fecha . ' ' . $horaInicioInput);
        if ($fechaReservaCompleta->isPast()) {
            return view('dashboard')->with([
                'error' => 'El horario seleccionado ya pasó. Por favor, elige una hora futura.',
                'canchas' => collect(), 'fecha' => $fecha, 'horaInicioInput' => $horaInicioInput, 'duracionInput' => $duracionInput, 'totalPreview' => 0
            ]);
        }

        $carbonInicio = Carbon::createFromFormat('H:i', $horaInicioInput);
        
        if ($carbonInicio->hour + $duracionInput > 23) {
            return view('dashboard')->with([
                'error' => 'El club cierra a las 11:00 PM. No hay disponibilidad para ese rango.',
                'canchas' => collect(), 'fecha' => $fecha, 'horaInicioInput' => $horaInicioInput, 'duracionInput' => $duracionInput, 'totalPreview' => 0
            ]);
        }

        $horaFinCalculada = $carbonInicio->copy()->addHours($duracionInput)->format('H:i:s');
        $horaInicioFormateada = $carbonInicio->format('H:i:s');

        $canchasOcupadasIds = Reserva::where('fecha', $fecha)
            ->whereIn('estado', ['Pendiente', 'Verificado'])
            ->where(function($query) use ($horaInicioFormateada, $horaFinCalculada) {
                $query->where('hora_inicio', '<', $horaFinCalculada)->where('hora_fin', '>', $horaInicioFormateada);
            })->pluck('cancha_id');

        $canchas = Cancha::whereNotIn('id', $canchasOcupadasIds)->where('estado', 'Disponible')->get();

        $totalPreview = 0;
        for ($i = 0; $i < $duracionInput; $i++) {
            $horaEvaluada = $carbonInicio->copy()->addHours($i)->hour;
            $totalPreview += ($horaEvaluada >= 18) ? 60.00 : 50.00;
        }

        return view('dashboard', compact('canchas', 'fecha', 'horaInicioInput', 'duracionInput', 'totalPreview'));
    }

    public function reservar(Request $request)
    {
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required',
            'duracion' => 'required|in:1,2',
            'metodo_pago' => 'required|in:yape,efectivo',
            'numero_operacion' => 'required_if:metodo_pago,yape'
        ]);

        $fechaVerificacion = Carbon::parse($request->fecha . ' ' . $request->hora);
        if ($fechaVerificacion->isPast()) {
            return redirect()->back()->with('error', 'Error: No puedes reservar en un horario que ya pasó.');
        }

        $carbonInicio = Carbon::createFromFormat('H:i', $request->hora);

        if ($carbonInicio->hour + (int)$request->duracion > 23) {
            return redirect()->back()->with('error', 'Horario no permitido: El club cierra a las 11:00 PM.');
        }

        $reservasActivas = Reserva::where('user_id', Auth::id())->whereIn('estado', ['Pendiente', 'Verificado'])->count();
        if ($reservasActivas >= 3) {
            return redirect()->back()->with('error', 'Límite superado: No puedes tener más de 3 reservas activas simultáneamente.');
        }

        $horaInicio = $carbonInicio->format('H:i:s');
        $horaFin = $carbonInicio->copy()->addHours((int)$request->duracion)->format('H:i:s');

        return DB::transaction(function () use ($request, $horaInicio, $horaFin, $carbonInicio) {
            
            // SOLUCIÓN "CHOQUE FANTASMA" (lockForUpdate)
            $cruceHorario = Reserva::where('cancha_id', $request->cancha_id)->where('fecha', $request->fecha)
                ->whereIn('estado', ['Pendiente', 'Verificado'])
                ->where(function($query) use ($horaInicio, $horaFin) {
                    $query->where('hora_inicio', '<', $horaFin)->where('hora_fin', '>', $horaInicio);
                })
                ->lockForUpdate() 
                ->exists();

            if ($cruceHorario) {
                return redirect()->back()->with('error', 'Lo sentimos, alguien más acaba de tomar este horario exacto.');
            }

            $totalCobrar = 0;
            for ($i = 0; $i < (int)$request->duracion; $i++) {
                $horaEvaluada = $carbonInicio->copy()->addHours($i)->hour;
                $totalCobrar += ($horaEvaluada >= 18) ? 60.00 : 50.00;
            }

            Reserva::create([
                'user_id' => Auth::id(), 'cancha_id' => $request->cancha_id, 'fecha' => $request->fecha,
                'hora_inicio' => $horaInicio, 'hora_fin' => $horaFin, 'duracion' => $request->duracion,
                'estado' => 'Pendiente', 'metodo_pago' => $request->metodo_pago, 'numero_operacion' => $request->numero_operacion,
                'total' => $totalCobrar, 'monto_pagado' => $request->metodo_pago === 'yape' ? $totalCobrar : 0.00
            ]);

            return redirect()->route('dashboard')->with('success', $request->metodo_pago === 'yape' ? '¡Pre-reserva exitosa! Tienes 30 minutos para validarla.' : '¡Reserva en caja registrada!');
        });
    }

    public function cancelar($id)
    {
        $reserva = Reserva::findOrFail($id);
        if ($reserva->user_id !== Auth::id()) { return redirect()->back()->with('error', 'Acción no autorizada.'); }
        if (in_array($reserva->estado, ['Cancelada', 'Expirado', 'No_Show', 'Rechazado'])) {
            return redirect()->back()->with('error', 'Esta reserva ya no puede modificarse.');
        }

        $fechaReservaCompleta = Carbon::parse($reserva->fecha . ' ' . $reserva->hora_inicio);
        $ahora = Carbon::now();

        if ($ahora->greaterThanOrEqualTo($fechaReservaCompleta)) {
            return redirect()->back()->with('error', 'No puedes cancelar un partido que ya inició o terminó.');
        }

        $horasDiferencia = $ahora->diffInHours($fechaReservaCompleta, false);
        $esReciente = Carbon::now()->subMinutes(30)->lessThanOrEqualTo(Carbon::parse($reserva->created_at));
        
        $montoReembolso = 0.00;
        $mensajeAlerta = "Reserva cancelada con éxito.";

        if ($reserva->metodo_pago === 'yape') {
            if ($horasDiferencia >= 6 || $esReciente) {
                $montoReembolso = $reserva->total;
                $mensajeAlerta = "Cancelación gratuita aprobada. Reembolso total (S/. " . number_format($montoReembolso, 2) . ") pendiente.";
            } else {
                $montoReembolso = $reserva->total * 0.50;
                $mensajeAlerta = "Cancelación con penalidad (Menos de 6 horas). Reembolso del 50% (S/. " . number_format($montoReembolso, 2) . ").";
            }
        }

        $reserva->update(['estado' => 'Cancelada', 'monto_reembolso' => $montoReembolso, 'tipo_cancelacion' => 'usuario']);
        return redirect()->back()->with('success', $mensajeAlerta);
    }

    public function reprogramar(Request $request, $id)
    {
        $request->validate(['nueva_fecha' => 'required|date|after_or_equal:today', 'nueva_hora' => 'required']);
        $reserva = Reserva::findOrFail($id);

        if ($reserva->user_id !== Auth::id()) { return redirect()->back()->with('error', 'Acción no autorizada.'); }
        if ($reserva->reprogramaciones >= 2) { return redirect()->back()->with('error', 'Límite de reprogramaciones alcanzado.'); }

        $fechaNuevaCompleta = Carbon::parse($request->nueva_fecha . ' ' . $request->nueva_hora);
        if ($fechaNuevaCompleta->isPast()) {
            return redirect()->back()->with('error', 'Error: No puedes reprogramar hacia un horario que ya pasó.');
        }

        $fechaReservaOriginal = Carbon::parse($reserva->fecha . ' ' . $reserva->hora_inicio);
        $esReciente = Carbon::now()->subMinutes(30)->lessThanOrEqualTo(Carbon::parse($reserva->created_at));

        if (!$esReciente && Carbon::now()->diffInHours($fechaReservaOriginal, false) < 6) {
            return redirect()->back()->with('error', 'Solo puedes reprogramar con un mínimo de 6 horas de anticipación.');
        }

        $carbonInicio = Carbon::createFromFormat('H:i', $request->nueva_hora);
        if ($carbonInicio->hour + $reserva->duracion > 23) {
            return redirect()->back()->with('error', 'Horario no permitido: El club cierra a las 11:00 PM.');
        }

        $nuevaHoraInicio = $carbonInicio->format('H:i:s');
        $nuevaHoraFin = $carbonInicio->copy()->addHours($reserva->duracion)->format('H:i:s');

        $cruceHorario = Reserva::where('cancha_id', $reserva->cancha_id)->where('id', '!=', $reserva->id)
            ->where('fecha', $request->nueva_fecha)->whereIn('estado', ['Pendiente', 'Verificado'])
            ->where(function($query) use ($nuevaHoraInicio, $nuevaHoraFin) {
                $query->where('hora_inicio', '<', $nuevaHoraFin)->where('hora_fin', '>', $nuevaHoraInicio);
            })->exists();

        if ($cruceHorario) { return redirect()->back()->with('error', 'La cancha no está disponible en ese horario.'); }

        $nuevoTotal = 0;
        for ($i = 0; $i < $reserva->duracion; $i++) {
            $horaEvaluada = $carbonInicio->copy()->addHours($i)->hour;
            $nuevoTotal += ($horaEvaluada >= 18) ? 60.00 : 50.00;
        }

        // SOLUCIÓN "EL ROBO PERFECTO"
        if ($nuevoTotal > $reserva->total) {
            return redirect()->back()->with('error', 'No puedes reprogramar a un horario de mayor precio (Hora Punta). Cancela la reserva actual (pide reembolso) y genera una nueva.');
        }

        $reserva->update([
            'fecha' => $request->nueva_fecha, 'hora_inicio' => $nuevaHoraInicio, 'hora_fin' => $nuevaHoraFin,
            'total' => $nuevoTotal, 'reprogramaciones' => $reserva->reprogramaciones + 1
        ]);

        return redirect()->back()->with('success', 'Reserva reprogramada con éxito.');
    }

    public function eliminar($id)
    {
        $reserva = Reserva::findOrFail($id);
        if ($reserva->user_id !== Auth::id()) { return redirect()->back()->with('error', 'Acción no autorizada.'); }
        if (!in_array($reserva->estado, ['Cancelada', 'Expirado', 'No_Show', 'Rechazado'])) {
            return redirect()->back()->with('error', 'No puedes eliminar una reserva activa.');
        }
        $reserva->delete();
        return redirect()->back()->with('success', 'Ticket eliminado de tu historial correctamente.');
    }
}