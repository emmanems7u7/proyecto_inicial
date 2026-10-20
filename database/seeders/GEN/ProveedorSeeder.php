<?php

namespace Database\Seeders\GEN;

use App\Models\Empresa;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::orderBy('id')->firstOrFail();

        // [nit, nombre, contacto, ciudad, dias]
        $proveedores = [
            ['1001001011', 'Industrias Alimenticias del Sur S.A.', 'Carlos Medina', 'LPZ', 30],
            ['1001001022', 'Lácteos Altiplano S.R.L.', 'Rosa Limachi', 'LPZ', 15],
            ['1001001033', 'Embotelladora Boliviana Ltda.', 'Jorge Paz', 'CBB', 30],
            ['1001001044', 'Distribuidora de Limpieza Brillo', 'Sandra Vega', 'EAL', 15],
            ['1001001055', 'Aceites y Grasas Oriente S.A.', 'Luis Antelo', 'SCZ', 45],
            ['1001001066', 'Importadora Higiene Total', 'Patricia Rivera', 'SCZ', 30],
            ['1001001077', 'Molinos Andinos S.R.L.', 'Felipe Callisaya', 'LPZ', 0],
            ['1001001088', 'Jugos Naturales del Valle', 'Gladys Mendoza', 'CBB', 15],
        ];

        foreach ($proveedores as [$nit, $nombre, $contacto, $ciudad, $dias]) {
            Proveedor::updateOrCreate(['empresa_id' => $empresa->id, 'numero_documento' => $nit], [
                'tipo_documento_id' => CatalogoSisSeeder::id('tipo_documento', 'NIT'),
                'nombre' => $nombre,
                'contacto_nombre' => $contacto,
                'email' => 'ventas' . substr($nit, -3) . '@proveedor.bo',
                'telefono' => '2-' . random_int(2000000, 2999999),
                'direccion' => 'Parque Industrial #' . random_int(1, 99),
                'ciudad_id' => CatalogoSisSeeder::id('ciudad', $ciudad),
                'dias_credito' => $dias,
                'estado' => 1,
            ]);
        }
    }
}
