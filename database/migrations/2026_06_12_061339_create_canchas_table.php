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
        Schema::create('canchas', function (Blueprint $table) {
            $table->id(); // El ID que luego ocultaremos en las vistas
            $table->string('nombre'); // Ej: Cancha Principal
            $table->string('superficie'); // Ej: Arcilla, Césped, Cemento
            $table->string('estado')->default('Disponible'); // Disponible, Mantenimiento
            $table->timestamps();
            $table->softDeletes(); // ¡Regla de Tecsup: Borrado Seguro!
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canchas');
    }
};