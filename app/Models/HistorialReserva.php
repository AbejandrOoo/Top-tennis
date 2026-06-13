<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialReserva extends Model
{
    use HasFactory;

    // Estos campos guardan una nota sencilla de cada cambio importante
    // La idea es poder revisar luego que paso con una reserva
    protected $fillable = [
        'reserva_id',
        'accion',
        'fecha_registro',
        'descripcion'
    ];

    public function reserva()
    {
        // Cada registro del historial apunta a una reserva concreta
        // Asi se puede ver la historia completa desde la reserva principal
        return $this->belongsTo(Reserva::class);
    }
}
