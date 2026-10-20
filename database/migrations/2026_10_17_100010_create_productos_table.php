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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('categoria_producto_id')->nullable()->constrained('categorias_producto')->nullOnDelete();
            $table->foreignId('unidad_medida_id')->comment('Catálogo: unidad de medida')->constrained('catalogos');
            $table->string('codigo', 50);
            $table->string('codigo_barras', 60)->nullable();
            $table->string('nombre', 180);
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->decimal('precio_compra', 14, 2)->default(0)->comment('Costo referencial');
            $table->decimal('precio_venta', 14, 2)->default(0);
            $table->boolean('maneja_lotes')->default(false);
            $table->boolean('maneja_vencimiento')->default(false);
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'codigo']);
            $table->index('codigo_barras');
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
