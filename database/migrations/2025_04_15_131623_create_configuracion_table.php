<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->boolean('doble_factor_autenticacion')->default(false)->comment('Activar o desactivar el doble factor de autenticacion');
            $table->text('GROQ_API_KEY')->nullable();
            $table->boolean('mantenimiento')->default(false)->comment('Activar o desactivar mantenimiento de sistema');
            $table->integer('limite_de_sesiones')
                ->default(1)
                ->comment('Número máximo de sesiones activas permitidas por usuario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
