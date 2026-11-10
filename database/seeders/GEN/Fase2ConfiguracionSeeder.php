<?php

namespace Database\Seeders\GEN;

use App\Models\Caja;
use App\Models\Tienda;
use App\Services\AutomatizacionService;
use Database\Seeders\Traits\RunsOnce;
use Illuminate\Database\Seeder;

/** Crea una caja por tienda (si no tiene) y la configuración de automatizaciones. */
class Fase2ConfiguracionSeeder extends Seeder
{
    use RunsOnce;

    protected function handle()
    {
        $tiendas = Tienda::where('estado', 1)->get();

        foreach ($tiendas as $tienda) {
            if (! Caja::where('tienda_id', $tienda->id)->exists()) {
                Caja::create(['tienda_id' => $tienda->id, 'codigo' => 'CAJA-01', 'nombre' => 'Caja principal', 'estado' => 1]);
            }
        }

        app(AutomatizacionService::class)->asegurarConfiguracion($tiendas);
    }
}
