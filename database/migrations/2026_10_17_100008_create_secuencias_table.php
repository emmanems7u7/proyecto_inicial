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
        Schema::create('secuencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas');
            $table->string('tipo', 30)->comment('venta, cotizacion, compra, orden_compra, transferencia, etc.');
            $table->string('prefijo', 10)->nullable();
            $table->unsignedBigInteger('ultimo_numero')->default(0);
            $table->unsignedTinyInteger('longitud')->default(6);
            $table->timestamps();

            $table->unique(['tienda_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secuencias');
    }
};
