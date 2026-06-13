<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use Carbon\Carbon;

class LimpiarReservasExpiradas extends Command
{
    // El nombre del comando para ejecutarlo en terminal
    protected $signature = 'reservas:limpiar';

    // Descripción para la consola
    protected $description = 'Limpia reservas de Yape expiradas (30 mins) y marca No Shows';

    public function handle()
    {
        $this->info('Iniciando limpieza de reservas...');

        // 1. Limpiar reservas Pendientes de Yape que pasaron los 30 min
        $tiempoLimite = Carbon::now()->subMinutes(30);
        $expiradas = Reserva::where('estado', 'Pendiente')
            ->where('metodo_pago', 'yape') 
            ->where('created_at', '<=', $tiempoLimite)
            ->update(['estado' => 'Expirado', 'tipo_cancelacion' => 'sistema']);

        // 2. Marcar "No Show" a los que no llegaron al partido
        $hoy = Carbon::now()->format('Y-m-d');
        $horaActual = Carbon::now()->format('H:i:s');
        
        $noShows = Reserva::where('estado', 'Verificado')
            ->where('ingresado', false) // Si tienes este campo para saber si llegaron a la cancha
            ->where(function($query) use ($hoy, $horaActual) {
                $query->where('fecha', '<', $hoy)
                      ->orWhere(function($q) use ($hoy, $horaActual) {
                          $q->where('fecha', $hoy)->where('hora_fin', '<', $horaActual);
                      });
            })->update(['estado' => 'No_Show']);

        $this->info("Limpieza completada. Expiradas: {$expiradas}. No Shows: {$noShows}.");
    }
}