<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarifa extends Model
{
    use HasFactory, SoftDeletes;

    // Actualizamos el fillable con las nuevas columnas de horas y estado
    protected $fillable = [
        'cancha_id', 
        'turno',       // Lo mantenemos por si otra parte de tu sistema aún lo lee
        'precio_hora',
        'hora_inicio', // Agregado para el cálculo exacto
        'hora_fin',    // Agregado para el cálculo exacto
        'estado'       // Agregado para el historial de activación/desactivación
    ];

    public function cancha()
    {
        // Cada tarifa pertenece a una cancha especifica del club
        // Esta relacion ayuda a mostrar nombres en vez de codigos
        return $this->belongsTo(Cancha::class);
    }
}