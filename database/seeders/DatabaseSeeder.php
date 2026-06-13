<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Usuario administrador para entrar al panel de control del sistema
        // Se usa updateOrCreate para no duplicarlo si el seeder corre otra vez
        User::updateOrCreate(
            ['email' => 'admin@toptennis.test'],
            [
                'name' => 'Administrador Top Tennis',
                'password' => Hash::make('password'),
                'rol' => 'admin',
            ]
        );

        // Usuario cliente para probar reservas desde la vista normal
        // Sirve para revisar el flujo sin crear cuentas a mano cada vez
        User::updateOrCreate(
            ['email' => 'cliente@toptennis.test'],
            [
                'name' => 'Cliente de Prueba',
                'password' => Hash::make('password'),
                'rol' => 'cliente',
            ]
        );
    }
}
