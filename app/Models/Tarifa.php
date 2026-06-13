<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarifa extends Model
{
    use HasFactory, SoftDeletes;

    // La tarifa guarda el precio por cancha y por turno
    // Con estos datos luego se calcula el total de una reserva
    protected $fillable = ['cancha_id', 'turno', 'precio_hora'];

    public function cancha()
    {
        // Cada tarifa pertenece a una cancha especifica del club
        // Esta relacion ayuda a mostrar nombres en vez de codigos
        return $this->belongsTo(Cancha::class);
    }
}
