<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventario extends Model
{
    protected $table = 'inventarios';

    protected $fillable = [
        'almacen_id', 'producto_id', 'cantidad', 'stock_minimo', 'stock_maximo',
        'ubicacion', 'alerta_stock_enviada_at',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'stock_minimo' => 'float',
        'stock_maximo' => 'float',
        'alerta_stock_enviada_at' => 'datetime',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function getBajoMinimoAttribute(): bool
    {
        return $this->stock_minimo > 0 && $this->cantidad <= $this->stock_minimo;
    }
}
