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
        'estado',
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

    // Estos eventos guardan un registro simple de lo que pasa con la reserva
    // Sirven para explicar despues quien cambio el estado y cuando ocurrio
    protected static function booted()
    {
        // Cuando nace una reserva se guarda el primer movimiento del historial
        // Con eso queda rastro del estado inicial y de la duracion pedida
        static::created(function ($reserva) {
            HistorialReserva::create([
                'reserva_id' => $reserva->id,
                'accion' => 'crear',
                'fecha_registro' => Carbon::now(),
                'descripcion' => "Reserva inicial creada en estado '{$reserva->estado}' por la duración de {$reserva->duracion} hora(s)."
            ]);
        });

        // Cuando se actualiza una reserva se revisan los cambios importantes
        // No todo cambio necesita historial pero estado y reprogramacion si
        static::updated(function ($reserva) {
            // Si cambia el estado se registra una accion entendible para el usuario
            // Tambien se guarda si hubo cancelacion o monto de reembolso
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

            // Si sube el contador de reprogramaciones se deja una nota aparte
            // Asi queda claro que la fecha u hora fueron movidas
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
        // Relacion con el cliente que hizo la reserva
        return $this->belongsTo(User::class);
    }

    public function cancha()
    {
        // Relacion con la cancha que se separo para jugar
        return $this->belongsTo(Cancha::class);
    }
}
