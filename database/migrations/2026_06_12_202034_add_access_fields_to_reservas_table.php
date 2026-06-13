<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('codigo_acceso')->nullable()->unique()->after('numero_operacion');
            $table->boolean('ingresado')->default(false)->after('estado'); // Controla si ya entró
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['codigo_acceso', 'ingresado']);
        });
    }
};