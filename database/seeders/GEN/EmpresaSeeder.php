<?php

namespace Database\Seeders\GEN;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::updateOrCreate(['nit' => '1020304050'], [
            'razon_social' => 'Distribuidora Andina S.R.L.',
            'nombre_comercial' => 'Andina Market',
            'email' => 'contacto@andinamarket.bo',
            'telefono' => '2-2456789',
            'direccion' => 'Av. 6 de Agosto #2345, Sopocachi',
            'ciudad_id' => CatalogoSisSeeder::id('ciudad', 'LPZ'),
            'estado' => 1,
        ]);
    }
}
