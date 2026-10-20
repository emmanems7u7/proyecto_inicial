<?php

namespace Database\Seeders\GEN;

use App\Models\CategoriaProducto;
use App\Models\Empresa;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::orderBy('id')->firstOrFail();

        // [codigo, nombre, categoria, unidad, compra, venta, lotes, vencimiento]
        $productos = [
            ['P-001', 'Arroz grano de oro 1 kg', 'Arroz y fideos', 'KG', 8.50, 10.50, false, false],
            ['P-002', 'Fideo tallarín 400 g', 'Arroz y fideos', 'UND', 4.00, 5.50, false, false],
            ['P-003', 'Aceite vegetal 900 ml', 'Abarrotes', 'UND', 14.00, 17.00, true, true],
            ['P-004', 'Azúcar blanca 1 kg', 'Abarrotes', 'KG', 6.00, 7.50, false, false],
            ['P-005', 'Leche entera 1 L', 'Lácteos', 'LT', 6.50, 8.00, true, true],
            ['P-006', 'Yogurt natural 1 L', 'Lácteos', 'LT', 12.00, 15.00, true, true],
            ['P-007', 'Gaseosa cola 2 L', 'Gaseosas', 'UND', 9.00, 12.00, true, true],
            ['P-008', 'Jugo de durazno 1 L', 'Jugos', 'LT', 7.00, 9.50, true, true],
            ['P-009', 'Agua mineral 600 ml', 'Bebidas', 'UND', 2.00, 3.50, false, false],
            ['P-010', 'Detergente en polvo 1 kg', 'Limpieza', 'KG', 15.00, 19.00, false, false],
            ['P-011', 'Lavandina 1 L', 'Limpieza', 'LT', 5.00, 7.00, false, false],
            ['P-012', 'Jabón de tocador x3', 'Cuidado personal', 'CJA', 10.00, 13.00, false, false],
        ];

        foreach ($productos as [$codigo, $nombre, $cat, $uni, $compra, $venta, $lotes, $venc]) {
            Producto::updateOrCreate(['empresa_id' => $empresa->id, 'codigo' => $codigo], [
                'categoria_producto_id' => CategoriaProducto::where('empresa_id', $empresa->id)->where('nombre', $cat)->value('id'),
                'unidad_medida_id' => CatalogoSisSeeder::id('unidad_medida', $uni),
                'codigo_barras' => '77' . str_pad((string) crc32($codigo), 11, '0', STR_PAD_LEFT),
                'nombre' => $nombre,
                'descripcion' => $nombre,
                'precio_compra' => $compra,
                'precio_venta' => $venta,
                'maneja_lotes' => $lotes,
                'maneja_vencimiento' => $venc,
                'estado' => 1,
            ]);
        }
    }
}
