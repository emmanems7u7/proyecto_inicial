<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Parte 2 de ajustes:
 *  - productos: marca y stock mínimo por defecto.
 *  - presentaciones_catalogo: catálogo de presentaciones de la empresa (Unidad, Media caja, Caja...). Cada producto
 *    elige del catálogo y personaliza cuántas unidades trae y sus precios (tabla producto_presentaciones de la parte 1).
 *  - almacenes: tipo (principal / exhibicion / merma), encargado y ubicaciones (racks o pasillos).
 *  - compra_detalles: presentación comprada (la cantidad y el costo se guardan en unidad base, igual que en ventas).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $t) {
            if (! Schema::hasColumn('productos', 'marca')) $t->string('marca', 80)->nullable();
            if (! Schema::hasColumn('productos', 'stock_minimo')) $t->decimal('stock_minimo', 12, 3)->default(0);
        });

        if (! Schema::hasTable('presentaciones_catalogo')) {
            Schema::create('presentaciones_catalogo', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('empresa_id');
                $t->string('nombre', 60);
                $t->decimal('unidades', 12, 3)->default(1);   // cantidad sugerida; cada producto la personaliza
                $t->tinyInteger('estado')->default(1);
                $t->timestamps();
                $t->unique(['empresa_id', 'nombre']);
            });

            $base = [['Unidad', 1], ['Media caja', 12], ['Caja', 24], ['Paquete', 6], ['Docena', 12], ['Bolsa', 1]];
            if (Schema::hasTable('empresas')) {
                foreach (DB::table('empresas')->pluck('id') as $empresaId) {
                    foreach ($base as [$nombre, $unidades]) {
                        DB::table('presentaciones_catalogo')->insert([
                            'empresa_id' => $empresaId, 'nombre' => $nombre, 'unidades' => $unidades,
                            'estado' => 1, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        Schema::table('almacenes', function (Blueprint $t) {
            if (! Schema::hasColumn('almacenes', 'tipo')) $t->string('tipo', 20)->default('principal');
            if (! Schema::hasColumn('almacenes', 'encargado_id')) $t->unsignedBigInteger('encargado_id')->nullable();
            if (! Schema::hasColumn('almacenes', 'ubicaciones')) $t->text('ubicaciones')->nullable();
        });

        Schema::table('compra_detalles', function (Blueprint $t) {
            if (! Schema::hasColumn('compra_detalles', 'presentacion_id')) $t->unsignedBigInteger('presentacion_id')->nullable();
            if (! Schema::hasColumn('compra_detalles', 'presentacion_nombre')) $t->string('presentacion_nombre', 60)->nullable();
            if (! Schema::hasColumn('compra_detalles', 'factor')) $t->decimal('factor', 12, 3)->default(1);
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE compra_detalles MODIFY costo_unitario DECIMAL(14,6) NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        Schema::table('compra_detalles', function (Blueprint $t) {
            foreach (['presentacion_id', 'presentacion_nombre', 'factor'] as $c) {
                if (Schema::hasColumn('compra_detalles', $c)) $t->dropColumn($c);
            }
        });
        Schema::table('almacenes', function (Blueprint $t) {
            foreach (['tipo', 'encargado_id', 'ubicaciones'] as $c) {
                if (Schema::hasColumn('almacenes', $c)) $t->dropColumn($c);
            }
        });
        Schema::dropIfExists('presentaciones_catalogo');
        Schema::table('productos', function (Blueprint $t) {
            foreach (['marca', 'stock_minimo'] as $c) {
                if (Schema::hasColumn('productos', $c)) $t->dropColumn($c);
            }
        });
    }
};
