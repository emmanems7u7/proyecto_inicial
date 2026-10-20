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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad', 14, 3)->default(0);
            $table->decimal('stock_minimo', 14, 3)->default(0);
            $table->decimal('stock_maximo', 14, 3)->nullable();
            $table->string('ubicacion', 60)->nullable();
            $table->timestamp('alerta_stock_enviada_at')->nullable()->comment('Evita repetir alertas de stock mínimo');
            $table->timestamps();

            $table->unique(['almacen_id', 'producto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
