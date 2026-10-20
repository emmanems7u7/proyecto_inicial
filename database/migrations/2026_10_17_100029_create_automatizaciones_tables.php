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
        Schema::create('automatizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->comment('Null = configuración global de la empresa')->nullable()->constrained('tiendas')->cascadeOnDelete();
            $table->string('codigo', 60)->comment('cierre_caja_automatico, alerta_stock_minimo, alerta_vencimiento_lotes, recordatorio_cxc, etc.');
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->string('frecuencia', 20)->default('diaria')->comment('diaria, cada_hora, evento');
            $table->time('hora_ejecucion')->nullable();
            $table->json('parametros')->nullable()->comment('Ej: dias_aviso_vencimiento, correos_destino');
            $table->timestamp('ultima_ejecucion_at')->nullable();
            $table->timestamps();

            $table->unique(['tienda_id', 'codigo']);
        });

        Schema::create('automatizacion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automatizacion_id')->constrained('automatizaciones')->cascadeOnDelete();
            $table->foreignId('tienda_id')->nullable()->constrained('tiendas')->nullOnDelete();
            $table->string('estado', 20)->comment('exitoso, error, omitido');
            $table->string('mensaje', 255)->nullable();
            $table->json('detalle')->nullable();
            $table->dateTime('iniciada_at');
            $table->dateTime('finalizada_at')->nullable();
            $table->timestamps();

            $table->index(['automatizacion_id', 'iniciada_at']);
            $table->index('estado');
        });

        Schema::create('cola_correos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->nullable()->constrained('tiendas')->nullOnDelete();
            $table->foreignId('automatizacion_id')->nullable()->constrained('automatizaciones')->nullOnDelete();
            $table->string('destinatario', 150);
            $table->string('asunto', 200);
            $table->string('plantilla', 100)->nullable();
            $table->json('datos')->nullable();
            $table->longText('cuerpo')->nullable();
            $table->string('estado', 20)->default('pendiente')->comment('pendiente, enviado, error');
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->text('error')->nullable();
            $table->dateTime('programado_at')->nullable();
            $table->dateTime('enviado_at')->nullable();
            $table->timestamps();

            $table->index(['estado', 'programado_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cola_correos');
        Schema::dropIfExists('automatizacion_logs');
        Schema::dropIfExists('automatizaciones');
    }
};
