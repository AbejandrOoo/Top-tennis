<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $fillable = [
        'user_id',
        'cancha_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'duracion',
        'estado', // 'Pendiente', 'Verificado', 'Rechazado', 'Expirado', 'No_Show', 'Cancelada'
        'metodo_pago',
        'numero_operacion',
        'codigo_acceso',
        'ingresado',
        'total',
        'monto_pagado',
        'monto_reembolso',
        'tipo_cancelacion',
        'reprogramaciones'
    ];

    // Eventos automáticos de Eloquent para el Historial (Auditoría)
    protected static function booted()
    {
        // Se ejecuta automáticamente al crear una reserva
        static::created(function ($reserva) {
            HistorialReserva::create([
                'reserva_id' => $reserva->id,
                'accion' => 'crear',
                'fecha_registro' => Carbon::now(),
                'descripcion' => "Reserva inicial creada en estado '{$reserva->estado}' por la duración de {$reserva->duracion} hora(s)."
            ]);
        });

        // Se ejecuta automáticamente al actualizar cualquier campo (cancelar, reprogramar, verificar)
        static::updated(function ($reserva) {
            // Detectamos qué campo cambió para registrar la acción exacta
            if ($reserva->isDirty('estado')) {
                $nuevoEstado = $reserva->estado;
                $accion = strtolower($nuevoEstado) === 'cancelada' ? 'cancelar' : (strtolower($nuevoEstado) === 'expirado' ? 'expirar' : 'editar');
                
                HistorialReserva::create([
                    'reserva_id' => $reserva->id,
                    'accion' => $accion,
                    'fecha_registro' => Carbon::now(),
                    'descripcion' => "El estado de la reserva cambió a '{$nuevoEstado}'. Tipo cancelación: " . ($reserva->tipo_cancelacion ?? 'N/A') . ". Reembolso asignado: S/. {$reserva->monto_reembolso}."
                ]);
            }

            if ($reserva->isDirty('reprogramaciones')) {
                HistorialReserva::create([
                    'reserva_id' => $reserva->id,
                    'accion' => 'reprogramar',
                    'fecha_registro' => Carbon::now(),
                    'descripcion' => "Reserva reprogramada exitosamente. Nueva fecha: {$reserva->fecha} e inicio: {$reserva->hora_inicio}. Contador de cambios: {$reserva->reprogramaciones}."
                ]);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cancha()
    {
        return $this->belongsTo(Cancha::class);
    }
}