<?php

namespace App\Services;

use App\Models\Secuencia;
use App\Models\Tienda;
use Illuminate\Support\Facades\DB;

/** Numeración correlativa por tienda y tipo de documento: V-TDA01-000001 */
class SecuenciaService
{
    public function siguiente(int $tiendaId, string $tipo, string $prefijo, int $longitud = 6): string
    {
        return DB::transaction(function () use ($tiendaId, $tipo, $prefijo, $longitud) {
            $base = Secuencia::firstOrCreate(
                ['tienda_id' => $tiendaId, 'tipo' => $tipo],
                ['prefijo' => $prefijo, 'ultimo_numero' => 0, 'longitud' => $longitud]
            );

            $secuencia = Secuencia::lockForUpdate()->findOrFail($base->id);
            $secuencia->ultimo_numero = $secuencia->ultimo_numero + 1;
            $secuencia->save();

            $codigoTienda = Tienda::whereKey($tiendaId)->value('codigo');

            return ($secuencia->prefijo ?: $prefijo) . '-' . $codigoTienda . '-'
                . str_pad((string) $secuencia->ultimo_numero, $secuencia->longitud, '0', STR_PAD_LEFT);
        });
    }
}
