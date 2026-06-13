<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento; 
use App\Models\Cancha;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminMantenimientoController extends Controller
{
    public function index()
    {
        // Carga mantenimientos con sus canchas para una vista clara
        $mantenimientos = Mantenimiento::with('cancha')->orderBy('fecha_inicio', 'desc')->get();
        return view('admin.mantenimientos.index', compact('mantenimientos'));
    }

    public function create()
    {
        $canchas = Cancha::all();
        return view('admin.mantenimientos.create', compact('canchas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            'fecha_inicio' => 'required|date|after_or_equal:now',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'descripcion' => 'required|string|max:255',
        ]);

        // Transacción para asegurar consistencia: o se hace todo, o no se hace nada.
        DB::transaction(function () use ($request) {
            $this->validarSolapamiento($request->cancha_id, $request->fecha_inicio, $request->fecha_fin);

            $mantenimiento = Mantenimiento::create([
                'cancha_id' => $request->cancha_id,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'descripcion' => $request->descripcion,
                'estado' => 'Programado', // Estado inicial por defecto
            ]);

            // Desactivar la cancha y cancelar reservas afectadas
            $this->gestionarImpactoMantenimiento($mantenimiento);
        });

        return redirect()->route('admin.mantenimientos.index')->with('success', 'Mantenimiento programado y reservas afectadas gestionadas.');
    }

    public function edit(Mantenimiento $mantenimiento)
    {
        $canchas = Cancha::all();
        return view('admin.mantenimientos.edit', compact('mantenimiento', 'canchas'));
    }

    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        $request->validate([
            'cancha_id' => 'required|exists:canchas,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'descripcion' => 'required|string|max:255',
            'estado' => 'required|in:Programado,En Proceso,Finalizado,Cancelado',
        ]);

        DB::transaction(function () use ($request, $mantenimiento) {
            $this->validarSolapamiento($request->cancha_id, $request->fecha_inicio, $request->fecha_fin, $mantenimiento->id);

            $mantenimiento->update($request->all());

            // Si el mantenimiento está activo, asegurarse que la cancha esté bloqueada
            if (in_array($mantenimiento->estado, ['Programado', 'En Proceso'])) {
                $this->gestionarImpactoMantenimiento($mantenimiento);
            } elseif ($mantenimiento->estado === 'Finalizado' || $mantenimiento->estado === 'Cancelado') {
                // Si el mantenimiento termina o se cancela, la cancha vuelve a estar disponible
                $mantenimiento->cancha->update(['estado' => 'Disponible']);
            }
        });

        return redirect()->route('admin.mantenimientos.index')->with('success', 'Mantenimiento actualizado.');
    }

    public function destroy(Mantenimiento $mantenimiento)
    {
        // En lugar de borrar, se cancela para mantener el historial
        $mantenimiento->update(['estado' => 'Cancelado']);
        // La cancha vuelve a estar disponible
        $mantenimiento->cancha->update(['estado' => 'Disponible']);

        return redirect()->route('admin.mantenimientos.index')->with('success', 'Mantenimiento cancelado. La cancha ha sido habilitada.');
    }

    private function validarSolapamiento($cancha_id, $inicio, $fin, $ignorarId = null)
    {
        $query = Mantenimiento::where('cancha_id', $cancha_id)
            ->where(function ($q) use ($inicio, $fin) {
                $q->where('fecha_inicio', '<', $fin)
                  ->where('fecha_fin', '>', $inicio);
            })
            ->whereNotIn('estado', ['Finalizado', 'Cancelado']);

        if ($ignorarId) {
            $query->where('id', '!=', $ignorarId);
        }

        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'fecha_inicio' => 'El rango de fechas se solapa con otro mantenimiento activo para esta cancha.',
            ]);
        }
    }

    private function gestionarImpactoMantenimiento(Mantenimiento $mantenimiento)
    {
        // 1. Poner la cancha en estado "En Mantenimiento"
        $mantenimiento->cancha->update(['estado' => 'En Mantenimiento']);

        // 2. Buscar reservas activas que caen dentro del periodo de mantenimiento
        $reservasAfectadas = Reserva::where('cancha_id', $mantenimiento->cancha_id)
            ->whereIn('estado', ['Pendiente', 'Verificado'])
            ->where(function ($query) use ($mantenimiento) {
                $fechaInicioMantenimiento = Carbon::parse($mantenimiento->fecha_inicio);
                $fechaFinMantenimiento = Carbon::parse($mantenimiento->fecha_fin);

                // Una reserva se ve afectada si su bloque de tiempo choca con el mantenimiento
                $query->where(function($q) use ($fechaInicioMantenimiento, $fechaFinMantenimiento) {
                    $q->where(DB::raw("CONCAT(fecha, ' ', hora_inicio)"), '<', $fechaFinMantenimiento)
                      ->where(DB::raw("CONCAT(fecha, ' ', hora_fin)"), '>', $fechaInicioMantenimiento);
                });
            })->get();

        // 3. Cancelar cada reserva afectada, asignando reembolso si corresponde
        foreach ($reservasAfectadas as $reserva) {
            $montoReembolso = 0.00;
            if ($reserva->metodo_pago === 'yape' && $reserva->estado === 'Verificado') {
                $montoReembolso = $reserva->total;
            }

            $reserva->update([
                'estado' => 'Cancelada',
                'monto_reembolso' => $montoReembolso,
                'tipo_cancelacion' => 'administrativa',
                'notas_cancelacion' => 'Cancelado por mantenimiento de cancha programado.'
            ]);
            
            // Aquí podrías agregar una notificación al usuario (email, etc.)
        }
    }
}
