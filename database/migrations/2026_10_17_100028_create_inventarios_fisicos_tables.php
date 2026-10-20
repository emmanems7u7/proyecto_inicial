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
        Schema::create('inventarios_fisicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('user_id')->constrained('users');
            $table->string('numero', 30)->unique();
            $table->date('fecha');
            $table->string('estado', 20)->default('en_proceso')->comment('en_proceso, finalizado, ajustado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('inventario_fisico_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_fisico_id')->constrained('inventarios_fisicos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->decimal('cantidad_sistema', 14, 3);
            $table->decimal('cantidad_contada', 14, 3);
            $table->decimal('diferencia', 14, 3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_fisico_detalles');
        Schema::dropIfExists('inventarios_fisicos');
    }
};
