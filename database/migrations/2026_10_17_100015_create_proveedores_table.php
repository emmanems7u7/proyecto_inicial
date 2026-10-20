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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('tipo_documento_id')->nullable()->constrained('catalogos')->nullOnDelete();
            $table->string('numero_documento', 30)->nullable();
            $table->string('nombre', 180);
            $table->string('contacto_nombre', 120)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->foreignId('ciudad_id')->nullable()->constrained('catalogos')->nullOnDelete();
            $table->unsignedSmallInteger('dias_credito')->default(0);
            $table->text('observaciones')->nullable();
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'numero_documento']);
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
