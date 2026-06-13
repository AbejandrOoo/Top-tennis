<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialReserva extends Model
{
    protected $table = 'historial_reservas';

    protected $fillable = [
        'reserva_id',
        'accion',
        'fecha_registro',
        'descripcion'
    ];

    /**
     * Relación inversa: Un historial le pertenece a una reserva.
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
}