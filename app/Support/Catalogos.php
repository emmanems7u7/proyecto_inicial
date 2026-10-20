<?php

namespace App\Support;

use App\Models\Catalogo;
use Illuminate\Support\Collection;

/**
 * Lectura de catálogos por prefijo de código (catalogo_codigo).
 * Devuelve [id => descripción] listo para los <select>.
 */
class Catalogos
{
    public static function porPrefijo(string $prefijo): Collection
    {
        return Catalogo::where('catalogo_codigo', 'like', $prefijo . '%')
            ->where('catalogo_estado', 1)
            ->orderBy('catalogo_descripcion')
            ->pluck('catalogo_descripcion', 'id');
    }

    public static function ciudades(): Collection        { return self::porPrefijo('C-'); }
    public static function unidades(): Collection        { return self::porPrefijo('UM-'); }
    public static function metodosPago(): Collection     { return self::porPrefijo('MP-'); }
    public static function tiposDocumento(): Collection  { return self::porPrefijo('TD-'); }
    public static function motivosAjuste(): Collection   { return self::porPrefijo('MA-'); }
}
