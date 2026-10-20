<?php

namespace Database\Seeders\GEN;

use App\Models\Almacen;
use App\Models\Tienda;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Tienda::all() as $tienda) {
            Almacen::updateOrCreate(['tienda_id' => $tienda->id, 'codigo' => 'ALM-01'], [
                'nombre' => 'Almacén principal',
                'direccion' => $tienda->direccion,
                'es_principal' => true,
                'estado' => 1,
            ]);

            Almacen::updateOrCreate(['tienda_id' => $tienda->id, 'codigo' => 'ALM-02'], [
                'nombre' => 'Almacén de reserva',
                'direccion' => $tienda->direccion,
                'es_principal' => false,
                'estado' => 1,
            ]);
        }
    }
}
