<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario administrador para acceder al panel /admin/dashboard.
        // updateOrCreate evita duplicados si ejecutamos el seeder varias veces.
        User::updateOrCreate(
            ['email' => 'admin@toptennis.test'],
            [
                'name' => 'Administrador Top Tennis',
                'password' => Hash::make('password'),
                'rol' => 'admin',
            ]
        );

        // Usuario cliente de prueba para revisar el flujo normal de reservas.
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
