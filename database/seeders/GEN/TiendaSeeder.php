<?php

namespace Database\Seeders\GEN;

use App\Models\Empresa;
use App\Models\Tienda;
use Illuminate\Database\Seeder;

class TiendaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::orderBy('id')->firstOrFail();

        $tiendas = [
            ['T-001', 'Casa Matriz Sopocachi', 'Av. 6 de Agosto #2345', 'LPZ', true],
            ['T-002', 'Sucursal El Alto', 'Av. Juan Pablo II #120', 'EAL', false],
            ['T-003', 'Sucursal Cochabamba', 'Av. América #456', 'CBB', false],
            ['T-004', 'Sucursal Santa Cruz', 'Av. Cristo Redentor #789', 'SCZ', false],
        ];

        foreach ($tiendas as [$codigo, $nombre, $dir, $ciudad, $matriz]) {
            Tienda::updateOrCreate(['empresa_id' => $empresa->id, 'codigo' => $codigo], [
                'nombre' => $nombre,
                'direccion' => $dir,
                'telefono' => '7' . random_int(1000000, 7999999),
                'email' => strtolower(str_replace('-', '', $codigo)) . '@andinamarket.bo',
                'ciudad_id' => CatalogoSisSeeder::id('ciudad', $ciudad),
                'es_matriz' => $matriz,
                'estado' => 1,
            ]);
        }
    }
}
