<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cancha_id',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'estado'
    ];

    // Para que Laravel trate estos campos como fechas (Carbon) y sea fácil hacer matemáticas con ellos
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function cancha()
    {
        return $this->belongsTo(Cancha::class);
    }
}