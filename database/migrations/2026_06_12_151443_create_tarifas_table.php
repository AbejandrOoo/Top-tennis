<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();
            
            // Llave foránea que conecta con la tabla canchas
            $table->foreignId('cancha_id')->constrained('canchas')->onDelete('cascade');
            
            // Datos de la tarifa
            $table->string('turno'); // Ej: Día, Noche, Fin de Semana
            $table->decimal('precio_hora', 8, 2); // Ej: 50.00
            
            $table->timestamps();
            $table->softDeletes(); // Borrado lógico para cumplir la rúbrica
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas');
    }
};