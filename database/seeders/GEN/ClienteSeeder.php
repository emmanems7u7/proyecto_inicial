<?php

namespace Database\Seeders\GEN;

use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::orderBy('id')->firstOrFail();

        // [tipo_doc, numero, nombre, ciudad, limite, dias]
        $clientes = [
            ['CI', '4567890', 'Juan Carlos Mamani Quispe', 'LPZ', 0, 0],
            ['CI', '5678901', 'María Elena Flores Condori', 'EAL', 500, 15],
            ['CI', '6789012', 'Pedro Luis Choque Apaza', 'LPZ', 1000, 30],
            ['CI', '7890123', 'Ana Gabriela Rojas Vargas', 'CBB', 0, 0],
            ['NIT', '1023456021', 'Minimarket El Ahorro S.R.L.', 'LPZ', 5000, 30],
            ['NIT', '1034567032', 'Restaurante Sabor Andino', 'EAL', 3000, 15],
            ['NIT', '1045678043', 'Hotel Illimani Ltda.', 'LPZ', 10000, 45],
            ['NIT', '1056789054', 'Comercial Los Pinos', 'SCZ', 4000, 30],
            ['CI', '8901234', 'Roberto Carlos Salinas Cruz', 'CBB', 0, 0],
            ['PAS', 'AB123456', 'Cliente Extranjero Demo', 'SCZ', 0, 0],
        ];

        foreach ($clientes as [$tipo, $num, $nombre, $ciudad, $limite, $dias]) {
            Cliente::updateOrCreate(['empresa_id' => $empresa->id, 'numero_documento' => $num], [
                'tipo_documento_id' => CatalogoSisSeeder::id('tipo_documento', $tipo),
                'nombre' => $nombre,
                'email' => 'cliente' . substr($num, 0, 4) . '@correo.bo',
                'telefono' => '7' . random_int(1000000, 7999999),
                'direccion' => 'Zona Central #' . random_int(10, 999),
                'ciudad_id' => CatalogoSisSeeder::id('ciudad', $ciudad),
                'limite_credito' => $limite,
                'dias_credito' => $dias,
                'estado' => 1,
            ]);
        }
    }
}
