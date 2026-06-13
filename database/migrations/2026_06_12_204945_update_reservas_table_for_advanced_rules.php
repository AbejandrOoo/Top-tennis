<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Modificamos y agregamos los campos solicitados
            if (Schema::hasColumn('reservas', 'hora_inicio')) {
                $table->time('hora_inicio')->change(); // Cambiamos a tipo TIME para cálculos matemáticos exactos
            }
            
            $table->time('hora_fin')->after('hora_inicio');
            $table->integer('duracion')->default(1)->after('hora_fin'); // 1 o 2 horas
            
            // Re-estructuramos estados: 'Pendiente', 'Verificado', 'Rechazado', 'Expirado', 'No_Show', 'Cancelada'
            $table->string('estado')->default('Pendiente')->change(); 
            
            // Campos financieros y de auditoría de cancelación/reprogramación
            $table->decimal('monto_pagado', 8, 2)->default(0.00)->after('total');
            $table->decimal('monto_reembolso', 8, 2)->default(0.00)->after('monto_pagado');
            $table->string('tipo_cancelacion')->nullable()->after('monto_reembolso'); // 'usuario' o 'admin'
            $table->integer('reprogramaciones')->default(0)->after('tipo_cancelacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['hora_fin', 'duracion', 'monto_pagado', 'monto_reembolso', 'tipo_cancelacion', 'reprogramaciones']);
        });
    }
};