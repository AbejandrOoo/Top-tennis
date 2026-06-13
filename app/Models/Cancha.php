<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cancha extends Model
{
    use HasFactory;

    protected $table = 'canchas';

    // Estos son los campos que se pueden guardar desde el formulario de canchas
    // Se dejan definidos para evitar cambios raros en columnas que no se editan
    protected $fillable = [
        'nombre',
        'superficie',
        'estado',
        'foto',
        'tipo_partido',
        'iluminacion',
        'descripcion'
    ];
}
