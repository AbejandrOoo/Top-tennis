<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cancha;
use App\Models\Reserva;
use App\Models\Tarifa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Si entra un administrador lo mandamos a su propio panel
        // Asi no se mezcla la vista de cliente con la vista de control
        if (Auth::user()->rol === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
        $fechaInput = $request->input('fecha');
        $fecha = $fechaInput ?: Carbon::now()->format('Y-m-d');
        $horaInicioInput = $request->input('hora');
        $duracionInput = (int) $request->input('duracion', 1);

        // Cuando el usuario entra sin filtros se propone una hora usable
        // La idea es evitar que el formulario cargue con un horario pasado
        if (!$horaInicioInput) {
            if ($fecha === Carbon::now()->format('Y-m-d')) {
                $horaSugerida = Carbon::now()->copy()->addHour()->hour;
                if ($horaSugerida >= 6 && $horaSugerida <= 22 && Carbon::now()->hour < 22) {
                    $horaInicioInput = sprintf('%02d:00', $horaSugerida);
                } else {
                    // Si ya es muy tarde o la hora sugerida no entra en el rango, pasamos a mañana si no forzó la fecha
                    if (!$fechaInput) {
                        $fecha = Carbon::now()->addDay()->format('Y-m-d');
                        $horaInicioInput = '06:00';
                    } else {
                        // Forzó la fecha de hoy pero ya es tarde. Se manda a 22:00 para que la validación de cierre de club o pasada lo ataje con un mensaje claro.
                        $horaInicioInput = '22:00';
                    }
                }
            } else {
                // Si la fecha solicitada es anterior a hoy, la validación isPast lo atrapará
                $horaInicioInput = '06:00';
            }
        }

        // Antes de mostrar canchas revisamos que la fecha todavia sirva
        // Si el horario ya paso no tiene sentido buscar disponibilidad
        $fechaReservaCompleta = Carbon::parse($fecha . ' ' . $horaInicioInput);
        if ($fechaReservaCompleta->isPast()) {
            $mensajeError = $request->has('hora') 
                ? 'El horario seleccionado (' . $horaInicioInput . ') ya pasó. Por favor, elige una hora futura.' 
                : 'Ya no hay horarios disponibles para la fecha seleccionada. Por favor, elige otra fecha.';
                
            return view('dashboard')->with([
                'error' => $mensajeError,
                'canchas' => collect(), 'fecha' => $fecha, 'horaInicioInput' => $horaInicioInput, 'duracionInput' => $duracionInput, 'totalPreview' => 0
            ]);
        }

        $carbonInicio = Carbon::createFromFormat('H:i', $horaInicioInput);
        
        // La reserva no debe pasarse del cierre del club
        // Por eso se corta el flujo antes de consultar canchas libres
        if ($carbonInicio->hour + $duracionInput > 23) {
            return view('dashboard')->with([
                'error' => 'El club cierra a las 11:00 PM. No hay disponibilidad para ese rango.',
                'canchas' => collect(), 'fecha' => $fecha, 'horaInicioInput' => $horaInicioInput, 'duracionInput' => $duracionInput, 'totalPreview' => 0
            ]);
        }

        $horaFinCalculada = $carbonInicio->copy()->addHours($duracionInput)->format('H:i:s');
        $horaInicioFormateada = $carbonInicio->format('H:i:s');

        // Buscamos reservas que choquen con el rango pedido por el cliente
        // Con esto se filtran las canchas que ya estan ocupadas
        $canchasOcupadasIds = Reserva::where('fecha', $fecha)
            ->whereIn('estado', ['Pendiente', 'Verificado'])
            ->where(function($query) use ($horaInicioFormateada, $horaFinCalculada) {
                $query->where('hora_inicio', '<', $horaFinCalculada)->where('hora_fin', '>', $horaInicioFormateada);
            })->pluck('cancha_id');

        $canchas = Cancha::whereNotIn('id', $canchasOcupadasIds)->where('estado', 'Disponible')->get();

        // A cada cancha libre se le agrega su total para mostrarlo en pantalla
        // El calculo sale de tarifas y mantiene un respaldo si falta algun dato
        $canchas->each(function ($cancha) use ($carbonInicio, $duracionInput) {
            $cancha->total_reserva = $this->calcularTotalReserva($cancha->id, $carbonInicio, $duracionInput);
        });

        return view('dashboard', compact('canchas', 'fecha', 'horaInicioInput', 'duracionInput'));
    }

    public function reservar(Request $request)
    {
        // Validamos lo minimo antes de crear la reserva
        // Estos datos vienen del modal de pago del dashboard
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required',
            'duracion' => 'required|in:1,2',
            'metodo_pago' => 'required|in:yape,efectivo',
            'numero_operacion' => 'required_if:metodo_pago,yape'
        ]);

        // Segunda revision de fecha para evitar reservas viejas enviadas a mano
        // Esto protege aunque alguien cambie datos desde el navegador
        $fechaVerificacion = Carbon::parse($request->fecha . ' ' . $request->hora);
        if ($fechaVerificacion->isPast()) {
            return redirect()->back()->with('error', 'Error: No puedes reservar en un horario que ya pasó.');
        }

        $carbonInicio = Carbon::createFromFormat('H:i', $request->hora);

        // Se vuelve a revisar el cierre del club antes de guardar
        // Asi el backend mantiene la regla aunque falle el formulario
        if ($carbonInicio->hour + (int)$request->duracion > 23) {
            return redirect()->back()->with('error', 'Horario no permitido: El club cierra a las 11:00 PM.');
        }

        // El cliente no debe acumular demasiadas reservas abiertas
        // Esta regla mantiene controlado el uso de las canchas
        $reservasActivas = Reserva::where('user_id', Auth::id())->whereIn('estado', ['Pendiente', 'Verificado'])->count();
        if ($reservasActivas >= 3) {
            return redirect()->back()->with('error', 'Límite superado: No puedes tener más de 3 reservas activas simultáneamente.');
        }

        $horaInicio = $carbonInicio->format('H:i:s');
        $horaFin = $carbonInicio->copy()->addHours((int)$request->duracion)->format('H:i:s');

        // Todo el guardado se hace dentro de una transaccion
        // Asi la revision de choque y la creacion quedan juntas
        return DB::transaction(function () use ($request, $horaInicio, $horaFin, $carbonInicio) {
            
            // Esta revision bloquea el horario mientras se confirma la reserva
            // Sirve para reducir cruces cuando dos usuarios reservan a la vez
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

            // El total sale de tarifas para que el administrador controle precios
            // Si falta una tarifa se mantiene un precio de respaldo
            $totalCobrar = $this->calcularTotalReserva($request->cancha_id, $carbonInicio, (int) $request->duracion);

            Reserva::create([
                'user_id' => Auth::id(), 'cancha_id' => $request->cancha_id, 'fecha' => $request->fecha,
                'hora_inicio' => $horaInicio, 'hora_fin' => $horaFin, 'duracion' => $request->duracion,
                'estado' => 'Pendiente', 'metodo_pago' => $request->metodo_pago, 'numero_operacion' => $request->numero_operacion,
                'total' => $totalCobrar, 'monto_pagado' => $request->metodo_pago === 'yape' ? $totalCobrar : 0.00
            ]);

            return redirect()->route('dashboard', [
                'fecha' => $request->fecha, 
                'hora' => $request->hora,
                'duracion' => $request->duracion
            ])->with('success', $request->metodo_pago === 'yape' ? '¡Pre-reserva exitosa! Tienes 30 minutos para validarla.' : '¡Reserva en caja registrada!');
        });
    }

    public function cancelar($id)
    {
        // Primero ubicamos la reserva y revisamos que sea del usuario actual
        // Luego se aplican las reglas de tiempo y reembolso
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

        // Solo las reservas pagadas por Yape manejan monto de reembolso
        // En efectivo no se devuelve dinero desde esta pantalla
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
        // Para reprogramar solo necesitamos nueva fecha y nueva hora
        // Lo demas se conserva desde la reserva original
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

        // Antes de mover la reserva se revisa que el nuevo horario este libre
        // Se ignora la misma reserva para no chocar contra ella misma
        $cruceHorario = Reserva::where('cancha_id', $reserva->cancha_id)->where('id', '!=', $reserva->id)
            ->where('fecha', $request->nueva_fecha)->whereIn('estado', ['Pendiente', 'Verificado'])
            ->where(function($query) use ($nuevaHoraInicio, $nuevaHoraFin) {
                $query->where('hora_inicio', '<', $nuevaHoraFin)->where('hora_fin', '>', $nuevaHoraInicio);
            })->exists();

        if ($cruceHorario) { return redirect()->back()->with('error', 'La cancha no está disponible en ese horario.'); }

        // Se recalcula el precio porque el nuevo horario puede tener otro turno
        // Esta parte respeta las tarifas que manejo el administrador
        $nuevoTotal = $this->calcularTotalReserva($reserva->cancha_id, $carbonInicio, $reserva->duracion);

        // No se permite pasar a un horario mas caro desde la misma reserva
        // Para ese caso el cliente debe cancelar y crear una reserva nueva
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
        // El historial visible solo se puede limpiar si la reserva ya no esta activa
        // Asi evitamos que el cliente borre una reserva pendiente por error
        $reserva = Reserva::findOrFail($id);
        if ($reserva->user_id !== Auth::id()) { return redirect()->back()->with('error', 'Acción no autorizada.'); }
        if (!in_array($reserva->estado, ['Cancelada', 'Expirado', 'No_Show', 'Rechazado'])) {
            return redirect()->back()->with('error', 'No puedes eliminar una reserva activa.');
        }
        $reserva->delete();
        return redirect()->back()->with('success', 'Ticket eliminado de tu historial correctamente.');
    }

    private function calcularTotalReserva(int $canchaId, Carbon $horaInicio, int $duracion): float
    {
        $total = 0;

        // Sumamos cada hora por separado porque una reserva puede cruzar turnos
        // De esta forma el total queda mas cercano a la tarifa real
        for ($i = 0; $i < $duracion; $i++) {
            $horaEvaluada = $horaInicio->copy()->addHours($i)->hour;
            $turno = $this->determinarTurno($horaEvaluada);

            $precio = Tarifa::where('cancha_id', $canchaId)
                ->where('turno', $turno)
                ->value('precio_hora');

            $total += $precio ?? $this->precioRespaldoPorHora($horaEvaluada);
        }

        return (float) $total;
    }

    private function determinarTurno(int $hora): string
    {
        // Se usa la misma division simple de turnos que ve el administrador
        // Esto conecta el horario elegido con el precio guardado
        if ($hora < 12) {
            return 'Mañana';
        }

        if ($hora < 18) {
            return 'Tarde';
        }

        return 'Noche';
    }

    private function precioRespaldoPorHora(int $hora): float
    {
        // Precio de respaldo para no romper reservas si falta configurar tarifas
        // Es mejor mostrar un total base que bloquear todo el flujo
        return $hora >= 18 ? 60.00 : 50.00;
    }
}
