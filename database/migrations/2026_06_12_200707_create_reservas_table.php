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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            
            // Relación con el usuario que reserva (llave foránea automática)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Relación con la cancha reservada (llave foránea automática)
            $table->foreignId('cancha_id')->constrained('canchas')->onDelete('cascade');
            
            // Detalles de la reserva
            $table->date('fecha');
            $table->string('hora_inicio'); // Guardará los bloques ej: '08:00', '10:00'
            $table->string('estado')->default('Pendiente'); // Estados: Pendiente, Aprobada, Cancelada
            $table->string('metodo_pago'); // 'yape' o 'efectivo'
            $table->string('numero_operacion')->nullable(); // Guardará el número de operación del voucher de Yape
            $table->decimal('total', 8, 2)->default(50.00); // Precio por hora de la cancha
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};