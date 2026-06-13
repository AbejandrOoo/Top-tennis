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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            // Relación con la cancha
            $table->foreignId('cancha_id')->constrained()->onDelete('cascade');
            
            // Fechas exactas del bloqueo
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            
            // Razón del mantenimiento (ej. "Pintura del piso")
            $table->string('motivo');
            
            // Estados exactos solicitados en tu requerimiento
            $table->enum('estado', ['Programado', 'En proceso', 'Finalizado', 'Cancelado'])->default('Programado');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
