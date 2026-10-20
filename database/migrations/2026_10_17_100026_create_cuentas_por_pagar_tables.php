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
        Schema::create('cuentas_por_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('compra_id')->nullable()->constrained('compras')->nullOnDelete();
            $table->string('numero_documento', 40);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->decimal('monto_total', 14, 2);
            $table->decimal('monto_pagado', 14, 2)->default(0);
            $table->decimal('saldo', 14, 2);
            $table->string('estado', 20)->default('pendiente')->comment('pendiente, parcial, pagada, vencida, anulada');
            $table->timestamp('ultima_alerta_at')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['estado', 'fecha_vencimiento']);
            $table->index(['tienda_id', 'proveedor_id']);
        });

        Schema::create('cxp_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_por_pagar_id')->constrained('cuentas_por_pagar');
            $table->foreignId('caja_sesion_id')->nullable()->constrained('caja_sesiones')->nullOnDelete();
            $table->foreignId('metodo_pago_id')->comment('Catálogo: método de pago')->constrained('catalogos');
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('monto', 14, 2);
            $table->dateTime('fecha');
            $table->string('referencia', 100)->nullable();
            $table->string('observacion', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cxp_pagos');
        Schema::dropIfExists('cuentas_por_pagar');
    }
};
