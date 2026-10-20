<?php

namespace App\Support;

use App\Models\Tienda;
use Illuminate\Support\Collection;

/**
 * Tienda "activa" del usuario y tiendas a las que tiene acceso.
 * - admin y administrador_general ven todas las tiendas de su empresa.
 * - el resto solo las asignadas en usuario_tienda.
 */
class TiendaContext
{
    public const ROLES_GLOBALES = ['admin', 'administrador_general'];

    public static function permitidas(): Collection
    {
        $user = auth()->user();
        if (! $user || ! $user->empresa_id) {
            return collect();
        }

        $cache = request()->attributes;
        if ($cache->has('tiendas_permitidas')) {
            return $cache->get('tiendas_permitidas');
        }

        $query = Tienda::where('empresa_id', $user->empresa_id)->where('estado', 1);

        if (! $user->hasAnyRole(self::ROLES_GLOBALES)) {
            $asignadas = $user->tiendas()->wherePivot('estado', 1)->get()->pluck('id');
            $query->whereIn('id', $asignadas);
        }

        $tiendas = $query->orderByDesc('es_matriz')->orderBy('nombre')->get();
        $cache->set('tiendas_permitidas', $tiendas);

        return $tiendas;
    }

    public static function ids(): array
    {
        return self::permitidas()->pluck('id')->all();
    }

    public static function actual(): ?Tienda
    {
        $permitidas = self::permitidas();
        if ($permitidas->isEmpty()) {
            return null;
        }

        $tienda = $permitidas->firstWhere('id', session('tienda_id'))
            ?? $permitidas->firstWhere('id', auth()->user()->tienda_principal_id)
            ?? $permitidas->first();

        session(['tienda_id' => $tienda->id]);

        return $tienda;
    }

    public static function establecer(int $tiendaId): bool
    {
        if (! self::permitidas()->contains('id', $tiendaId)) {
            return false;
        }
        session(['tienda_id' => $tiendaId]);

        return true;
    }
}
