<?php

namespace Database\Seeders\GEN;

use App\Models\CategoriaProducto;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class CategoriaProductoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::orderBy('id')->firstOrFail();

        // [nombre, descripcion, padre]
        $categorias = [
            ['Abarrotes', 'Productos de consumo básico', null],
            ['Bebidas', 'Bebidas en general', null],
            ['Lácteos', 'Leche, yogurt y derivados', null],
            ['Limpieza', 'Artículos de limpieza del hogar', null],
            ['Cuidado personal', 'Higiene y cuidado personal', null],
            ['Gaseosas', 'Bebidas carbonatadas', 'Bebidas'],
            ['Jugos', 'Jugos y néctares', 'Bebidas'],
            ['Arroz y fideos', 'Cereales y pastas', 'Abarrotes'],
        ];

        foreach ($categorias as [$nombre, $desc, $padre]) {
            $parentId = $padre
                ? CategoriaProducto::where('empresa_id', $empresa->id)->where('nombre', $padre)->value('id')
                : null;

            CategoriaProducto::updateOrCreate(['empresa_id' => $empresa->id, 'nombre' => $nombre], [
                'descripcion' => $desc,
                'parent_id' => $parentId,
                'estado' => 1,
            ]);
        }
    }
}
