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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->string('tipo', 30)->comment('entrada, salida, ajuste, venta, compra, devolucion_venta, devolucion_compra, transferencia_entrada, transferencia_salida, inventario_fisico');
            $table->decimal('cantidad', 14, 3)->comment('Con signo: positivo entra, negativo sale');
            $table->decimal('saldo_anterior', 14, 3)->default(0);
            $table->decimal('saldo_posterior', 14, 3)->default(0);
            $table->decimal('costo_unitario', 14, 2)->default(0);
            $table->foreignId('motivo_id')->comment('Catálogo: motivo de ajuste')->nullable()->constrained('catalogos')->nullOnDelete();
            $table->nullableMorphs('referencia');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('observacion', 255)->nullable();
            $table->dateTime('fecha');
            $table->timestamps();

            $table->index(['almacen_id', 'producto_id', 'fecha']);
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
