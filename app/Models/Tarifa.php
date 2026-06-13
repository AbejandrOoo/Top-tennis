<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarifa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['cancha_id', 'turno', 'precio_hora'];

    // Una tarifa pertenece a una cancha específica
    public function cancha()
    {
        return $this->belongsTo(Cancha::class);
    }
}