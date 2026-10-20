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
        Schema::create('caja_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas');
            $table->foreignId('user_apertura_id')->constrained('users');
            $table->foreignId('user_cierre_id')->comment('Null si el cierre fue automático')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_apertura', 14, 2)->default(0);
            $table->decimal('total_ingresos', 14, 2)->default(0);
            $table->decimal('total_egresos', 14, 2)->default(0);
            $table->decimal('monto_esperado', 14, 2)->nullable();
            $table->decimal('monto_real', 14, 2)->nullable()->comment('Arqueo; en cierre automático = monto_esperado');
            $table->decimal('diferencia', 14, 2)->nullable();
            $table->string('tipo_cierre', 20)->nullable()->comment('manual, automatico');
            $table->string('estado', 20)->default('abierta')->comment('abierta, cerrada');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['caja_id', 'estado']);
            $table->index('fecha_apertura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_sesiones');
    }
};
