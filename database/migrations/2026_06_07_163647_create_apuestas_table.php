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
        Schema::create('apuestas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('partido_id')->constrained();

            $table->string('matricula', 20);
            $table->string('nombre_completo');

            $table->decimal('monto', 10, 2);

            $table->integer('prediccion_local');
            $table->integer('prediccion_visitante');

            $table->string('codigo_apuesta')->unique();

            $table->string('comprobante')->nullable();

            $table->enum('estado_pago', [
                'pendiente',
                'aprobado',
                'rechazado'
            ])->default('pendiente');

            $table->enum('estado_apuesta', [
                'pendiente',
                'ganada',
                'perdida'
            ])->default('pendiente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apuestas');
    }
};
