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
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('numero', 30)->unique();
            $table->string('tipo', 20)->comment('entre_almacenes, entre_sucursales');
            $table->foreignId('tienda_origen_id')->constrained('tiendas');
            $table->foreignId('almacen_origen_id')->constrained('almacenes');
            $table->foreignId('tienda_destino_id')->constrained('tiendas');
            $table->foreignId('almacen_destino_id')->constrained('almacenes');
            $table->foreignId('user_envia_id')->constrained('users');
            $table->foreignId('user_recibe_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha');
            $table->dateTime('fecha_recepcion')->nullable();
            $table->string('estado', 20)->default('pendiente')->comment('pendiente, en_transito, recibida, anulada');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('estado');
        });

        Schema::create('transferencia_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transferencia_id')->constrained('transferencias')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->decimal('cantidad', 14, 3);
            $table->decimal('cantidad_recibida', 14, 3)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencia_detalles');
        Schema::dropIfExists('transferencias');
    }
};
