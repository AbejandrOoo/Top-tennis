<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use Carbon\Carbon;

class LimpiarReservasExpiradas extends Command
{
    // Nombre corto para ejecutar esta limpieza desde la consola
    // Tambien se usa cuando Laravel lo programa en segundo plano
    protected $signature = 'reservas:limpiar';

    // Texto simple para reconocer el comando cuando se lista en terminal
    protected $description = 'Limpia reservas de Yape expiradas y marca No Shows';

    public function handle()
    {
        $this->info('Iniciando limpieza de reservas...');

        // Primero se vencen las reservas de Yape que quedaron esperando pago
        // Si pasan muchos minutos sin validar se libera ese espacio
        $tiempoLimite = Carbon::now()->subMinutes(30);
        $expiradas = Reserva::where('estado', 'Pendiente')
            ->where('metodo_pago', 'yape')
            ->where('created_at', '<=', $tiempoLimite)
            ->update(['estado' => 'Expirado', 'tipo_cancelacion' => 'sistema']);

        // Luego se revisan partidos verificados que ya terminaron
        // Si nadie hizo ingreso se marcan como no presentados
        $hoy = Carbon::now()->format('Y-m-d');
        $horaActual = Carbon::now()->format('H:i:s');

        $noShows = Reserva::where('estado', 'Verificado')
            ->where('ingresado', false)
            ->where(function($query) use ($hoy, $horaActual) {
                $query->where('fecha', '<', $hoy)
                      ->orWhere(function($q) use ($hoy, $horaActual) {
                          $q->where('fecha', $hoy)->where('hora_fin', '<', $horaActual);
                      });
            })->update(['estado' => 'No_Show']);

        $this->info("Limpieza completada. Expiradas: {$expiradas}. No Shows: {$noShows}.");
    }
}
