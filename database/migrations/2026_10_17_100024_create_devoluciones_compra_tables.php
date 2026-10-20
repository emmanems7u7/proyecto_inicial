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
        Schema::create('devoluciones_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas');
            $table->foreignId('compra_id')->constrained('compras');
            $table->foreignId('user_id')->constrained('users');
            $table->string('numero', 30);
            $table->date('fecha');
            $table->decimal('total', 14, 2)->default(0);
            $table->string('motivo', 255)->nullable();
            $table->string('estado', 20)->default('completada');
            $table->timestamps();

            $table->unique(['tienda_id', 'numero']);
        });

        Schema::create('devolucion_compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_compra_id')->constrained('devoluciones_compra')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->decimal('cantidad', 14, 3);
            $table->decimal('costo_unitario', 14, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devolucion_compra_detalles');
        Schema::dropIfExists('devoluciones_compra');
    }
};
