<?php

namespace Database\Seeders\GEN;

use App\Models\Almacen;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Stock inicial del almacén principal de la tienda matriz:
 * Inventario + Lote (si el producto maneja lotes) + Movimiento "entrada" (kardex).
 * Incluye casos de prueba: bajo mínimo, lote por vencer y lote vencido.
 */
class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::orderBy('id')->firstOrFail();
        $tienda = Tienda::where('empresa_id', $empresa->id)->where('es_matriz', true)->firstOrFail();
        $almacen = Almacen::where('tienda_id', $tienda->id)->where('es_principal', true)->firstOrFail();

        $productos = Producto::where('empresa_id', $empresa->id)->where('estado', 1)->orderBy('codigo')->get();

        DB::transaction(function () use ($productos, $almacen, $tienda) {
            foreach ($productos as $i => $producto) {
                // Evita duplicar si se ejecuta dos veces.
                if (Inventario::where('almacen_id', $almacen->id)->where('producto_id', $producto->id)->where('cantidad', '>', 0)->exists()) {
                    continue;
                }

                $cantidad = 40 + ($i * 10);
                // Caso "bajo mínimo": el 3er y 9no producto quedan con poco stock.
                if (in_array($i, [2, 8], true)) {
                    $cantidad = 4;
                }

                Inventario::updateOrCreate(
                    ['almacen_id' => $almacen->id, 'producto_id' => $producto->id],
                    ['cantidad' => $cantidad, 'stock_minimo' => 10, 'stock_maximo' => 200, 'ubicacion' => 'Estante ' . chr(65 + ($i % 4))]
                );

                $loteId = null;
                if ($producto->maneja_lotes) {
                    // Primer lote por vencer (+15 días), segundo vencido (-5), resto vigente (+180).
                    $dias = match (true) {
                        $i === 2 => 15,
                        $i === 4 => -5,
                        default => 180,
                    };

                    $lote = Lote::create([
                        'producto_id' => $producto->id,
                        'almacen_id' => $almacen->id,
                        'numero_lote' => 'L-' . now()->format('Ym') . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                        'fecha_ingreso' => today(),
                        'fecha_vencimiento' => $producto->maneja_vencimiento ? today()->addDays($dias) : null,
                        'cantidad_inicial' => $cantidad,
                        'cantidad_actual' => $cantidad,
                        'costo_unitario' => $producto->precio_compra,
                        'estado' => 1,
                    ]);
                    $loteId = $lote->id;
                }

                MovimientoInventario::create([
                    'tienda_id' => $tienda->id,
                    'almacen_id' => $almacen->id,
                    'producto_id' => $producto->id,
                    'lote_id' => $loteId,
                    'tipo' => 'entrada',
                    'cantidad' => $cantidad,
                    'saldo_anterior' => 0,
                    'saldo_posterior' => $cantidad,
                    'costo_unitario' => $producto->precio_compra,
                    'observacion' => 'Stock inicial (seeder)',
                    'fecha' => now(),
                ]);
            }
        });
    }
}
