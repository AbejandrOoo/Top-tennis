<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// El rol se puede llenar desde seeders para crear usuarios de prueba
// Esto ayuda a separar rapido la vista de cliente y la de administrador
#[Fillable(['name', 'email', 'password', 'rol'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        // El password se guarda cifrado y la fecha de verificacion queda como fecha real
        // Laravel usa estos casts cada vez que lee o escribe el usuario
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
