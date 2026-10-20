<?php

namespace Database\Seeders\GEN;

use App\Models\Almacen;
use App\Models\Empresa;
use App\Models\Tienda;
use App\Models\User;
use Database\Seeders\Traits\RunsOnce;
use Illuminate\Database\Seeder;

/**
 * Crea empresa, tienda matriz y almacén principal, y asigna a los usuarios
 * existentes que aún no tienen empresa. Edita los datos desde "Mi empresa".
 */

class EstructuraInicialSeeder extends Seeder
{
    use RunsOnce;

    protected function handle()
    {
        $empresa = Empresa::first() ?? Empresa::create([
            'razon_social' => 'Mi Empresa',
            'nombre_comercial' => 'Mi Empresa',
            'estado' => 1,
        ]);

        $tienda = Tienda::firstOrCreate(
            ['empresa_id' => $empresa->id, 'codigo' => 'TDA-01'],
            ['nombre' => 'Casa matriz', 'es_matriz' => true, 'estado' => 1]
        );

        Almacen::firstOrCreate(
            ['tienda_id' => $tienda->id, 'codigo' => 'ALM-01'],
            ['nombre' => 'Almacén principal', 'es_principal' => true, 'estado' => 1]
        );

        User::whereNull('empresa_id')->update([
            'empresa_id' => $empresa->id,
            'tienda_principal_id' => $tienda->id,
        ]);

        foreach (User::where('empresa_id', $empresa->id)->get() as $usuario) {
            $usuario->tiendas()->syncWithoutDetaching([$tienda->id => ['estado' => 1]]);
        }
    }
}
