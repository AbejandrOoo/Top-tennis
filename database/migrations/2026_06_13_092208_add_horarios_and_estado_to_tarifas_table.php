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
        Schema::table('tarifas', function (Blueprint $table) {
            // Agregamos las nuevas columnas
            $table->time('hora_inicio')->nullable()->after('precio_hora');
            $table->time('hora_fin')->nullable()->after('hora_inicio');
            $table->string('estado')->default('Activa')->after('hora_fin'); // Activa o Inactiva
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifas', function (Blueprint $table) {
            // Si nos arrepentimos, esto borra las columnas
            $table->dropColumn(['hora_inicio', 'hora_fin', 'estado']);
        });
    }
};