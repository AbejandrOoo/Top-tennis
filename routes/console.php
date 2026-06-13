<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; 

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// TU LIMPIADOR AUTOMÁTICO DE RESERVAS
// Se ejecutará en segundo plano cada minuto
Schedule::command('reservas:limpiar')->everyMinute();