<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialReserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'reserva_id',
        'accion',
        'fecha_registro',
        'descripcion'
    ];

    /**
     * Relación: Un historial pertenece a una reserva
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
}