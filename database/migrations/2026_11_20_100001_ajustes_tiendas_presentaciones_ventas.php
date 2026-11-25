<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Parte 1 de ajustes:
 *  - tiendas: rubro, prefijo de comprobante y pie del ticket (nueva tienda).
 *  - clientes: lista de precios (menor / mayor / distribuidor).
 *  - producto_presentaciones: catálogo de presentaciones por producto (Unidad, Media caja, Caja...).
 *    Cada producto existente recibe su presentación base "Unidad" con sus precios actuales.
 *  - venta_detalles: presentación vendida y precio unitario con más decimales (precio por presentación / factor).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('tiendas', function (Blueprint $t) {
            if (! Schema::hasColumn('tiendas', 'rubro')) $t->string('rubro', 80)->nullable();
            if (! Schema::hasColumn('tiendas', 'prefijo_comprobante')) $t->string('prefijo_comprobante', 10)->nullable();
            if (! Schema::hasColumn('tiendas', 'pie_ticket')) $t->string('pie_ticket', 255)->nullable();
        });

        Schema::table('clientes', function (Blueprint $t) {
            if (! Schema::hasColumn('clientes', 'lista_precio')) $t->string('lista_precio', 20)->default('menor');
        });

        if (! Schema::hasTable('producto_presentaciones')) {
            Schema::create('producto_presentaciones', function (Blueprint $t) {
                $t->id();
                $t->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
                $t->string('nombre', 60);
                $t->decimal('unidades', 12, 3)->default(1);       // cuántas unidades base contiene
                $t->boolean('es_base')->default(false);
                $t->decimal('precio_costo', 12, 2)->default(0);
                $t->decimal('precio_menor', 12, 2)->default(0);
                $t->decimal('precio_mayor', 12, 2)->default(0);
                $t->decimal('precio_distribuidor', 12, 2)->default(0);
                $t->string('nombre_popular', 120)->nullable();    // ayuda a encontrar el producto (ej. "jaba")
                $t->tinyInteger('estado')->default(1);
                $t->timestamps();
                $t->unique(['producto_id', 'nombre']);
            });

            DB::table('producto_presentaciones')->insertUsing(
                ['producto_id', 'nombre', 'unidades', 'es_base', 'precio_costo', 'precio_menor', 'precio_mayor', 'precio_distribuidor', 'estado', 'created_at', 'updated_at'],
                DB::table('productos')->selectRaw("id, 'Unidad', 1, 1, precio_compra, precio_venta, precio_venta, precio_venta, 1, ?, ?", [now(), now()])
            );
        }

        Schema::table('venta_detalles', function (Blueprint $t) {
            if (! Schema::hasColumn('venta_detalles', 'presentacion_id')) $t->unsignedBigInteger('presentacion_id')->nullable();
            if (! Schema::hasColumn('venta_detalles', 'presentacion_nombre')) $t->string('presentacion_nombre', 60)->nullable();
            if (! Schema::hasColumn('venta_detalles', 'factor')) $t->decimal('factor', 12, 3)->default(1);
        });

        // El precio por unidad base de una caja (22 / 24 = 0,916667) necesita más decimales.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE venta_detalles MODIFY precio_unitario DECIMAL(14,6) NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        Schema::table('venta_detalles', function (Blueprint $t) {
            foreach (['presentacion_id', 'presentacion_nombre', 'factor'] as $c) {
                if (Schema::hasColumn('venta_detalles', $c)) $t->dropColumn($c);
            }
        });
        Schema::dropIfExists('producto_presentaciones');
        Schema::table('clientes', function (Blueprint $t) {
            if (Schema::hasColumn('clientes', 'lista_precio')) $t->dropColumn('lista_precio');
        });
        Schema::table('tiendas', function (Blueprint $t) {
            foreach (['rubro', 'prefijo_comprobante', 'pie_ticket'] as $c) {
                if (Schema::hasColumn('tiendas', $c)) $t->dropColumn($c);
            }
        });
    }
};
