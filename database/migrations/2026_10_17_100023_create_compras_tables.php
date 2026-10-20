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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('orden_compra_id')->nullable()->constrained('ordenes_compra')->nullOnDelete();
            $table->foreignId('user_id')->comment('Quien recepciona')->constrained('users');
            $table->string('numero', 30);
            $table->string('numero_documento_proveedor', 40)->nullable()->comment('Factura o nota del proveedor');
            $table->dateTime('fecha');
            $table->string('tipo_pago', 10)->default('contado')->comment('contado, credito');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('pagado', 14, 2)->default(0);
            $table->decimal('saldo', 14, 2)->default(0);
            $table->string('estado', 20)->default('recibida')->comment('recibida, anulada');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tienda_id', 'numero']);
            $table->index(['tienda_id', 'fecha']);
        });

        Schema::create('compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->comment('Lote creado al recepcionar')->nullable()->constrained('lotes')->nullOnDelete();
            $table->string('numero_lote', 60)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('cantidad', 14, 3);
            $table->decimal('costo_unitario', 14, 2);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_detalles');
        Schema::dropIfExists('compras');
    }
};
