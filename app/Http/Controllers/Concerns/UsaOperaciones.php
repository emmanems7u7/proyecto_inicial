<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Almacen;
use App\Models\Tienda;
use App\Support\TiendaContext;

trait UsaOperaciones
{
    protected function tiendaActual(): Tienda
    {
        $tienda = TiendaContext::actual();
        abort_if(! $tienda, 403, 'No tienes una tienda activa asignada.');

        return $tienda;
    }

    /** Almacenes activos de las tiendas indicadas, con su tienda cargada. */
    protected function almacenesDe(array $tiendaIds)
    {
        return Almacen::with('tienda')
            ->whereIn('tienda_id', $tiendaIds)
            ->where('estado', 1)
            ->orderBy('tienda_id')
            ->orderByDesc('es_principal')
            ->orderBy('nombre')
            ->get();
    }

    protected function opcionesAlmacen($almacenes)
    {
        return $almacenes->mapWithKeys(fn ($a) => [$a->id => $a->nombre_completo]);
    }

    /** [producto_id => [almacen_id => cantidad]] para mostrar disponibilidad en los formularios. */
    protected function mapaStock(array $almacenIds): array
    {
        return \App\Models\Inventario::whereIn('almacen_id', $almacenIds)
            ->where('cantidad', '>', 0)
            ->get(['producto_id', 'almacen_id', 'cantidad'])
            ->groupBy('producto_id')
            ->map(fn ($g) => $g->pluck('cantidad', 'almacen_id'))
            ->toArray();
    }
}
