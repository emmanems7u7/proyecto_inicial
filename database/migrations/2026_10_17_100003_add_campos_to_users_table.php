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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            }
            if (!Schema::hasColumn('users', 'tienda_principal_id')) {
                $table->foreignId('tienda_principal_id')->nullable()->constrained('tiendas')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('empresa_id');
            $table->dropConstrainedForeignId('tienda_principal_id');
            $table->dropColumn(['documento', 'telefono', 'avatar', 'estado', 'ultimo_acceso_at']);
            $table->dropSoftDeletes();
        });
    }
};
