<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id', 'categoria_producto_id', 'unidad_medida_id', 'codigo', 'codigo_barras',
        'nombre', 'descripcion', 'imagen', 'precio_compra', 'precio_venta',
        'maneja_lotes', 'maneja_vencimiento', 'estado',
    ];

    protected $casts = [
        'maneja_lotes' => 'boolean',
        'maneja_vencimiento' => 'boolean',
        'precio_compra' => 'float',
        'precio_venta' => 'float',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_producto_id');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'unidad_medida_id');
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        if (! $termino) {
            return $query;
        }

        return $query->where(function ($w) use ($termino) {
            $w->where('nombre', 'like', "%{$termino}%")
                ->orWhere('codigo', 'like', "%{$termino}%")
                ->orWhere('codigo_barras', 'like', "%{$termino}%");
        });
    }
}
