<?php

namespace Database\Seeders\GEN;

use App\Models\Empresa;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Asigna a TODOS los usuarios existentes de la empresa a TODAS sus tiendas (para poder probar el cambio de tienda activa). */
class TiendaUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::orderBy('id')->firstOrFail();
        $usuarios = User::where('empresa_id', $empresa->id)->pluck('id');

        foreach (Tienda::where('empresa_id', $empresa->id)->get() as $tienda) {
            $tienda->usuarios()->syncWithoutDetaching(
                $usuarios->mapWithKeys(fn ($id) => [$id => ['estado' => 1]])->all()
            );
        }
    }
}
