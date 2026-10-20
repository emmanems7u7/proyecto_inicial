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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->string('numero_lote', 60);
            $table->date('fecha_ingreso');
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('cantidad_inicial', 14, 3);
            $table->decimal('cantidad_actual', 14, 3);
            $table->decimal('costo_unitario', 14, 2)->default(0);
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 2=agotado, 0=vencido/bloqueado');
            $table->timestamp('alerta_vencimiento_enviada_at')->nullable();
            $table->timestamps();

            $table->unique(['producto_id', 'almacen_id', 'numero_lote']);
            $table->index('fecha_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
