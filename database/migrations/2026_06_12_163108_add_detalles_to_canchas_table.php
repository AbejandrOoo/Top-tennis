<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->string('foto')->nullable(); // Guardará la ruta de la imagen
            $table->string('tipo_partido')->default('Ambos (Singles y Dobles)'); 
            $table->string('iluminacion')->default('Sin iluminación'); 
            $table->text('descripcion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->dropColumn(['foto', 'tipo_partido', 'iluminacion', 'descripcion']);
        });
    }
};