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
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_sesion_id')->constrained('caja_sesiones');
            $table->string('tipo', 10)->comment('ingreso, egreso');
            $table->string('concepto', 255);
            $table->decimal('monto', 14, 2);
            $table->foreignId('metodo_pago_id')->comment('Catálogo: método de pago')->constrained('catalogos');
            $table->nullableMorphs('referencia');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha');
            $table->timestamps();

            $table->index(['caja_sesion_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};
