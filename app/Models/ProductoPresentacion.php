<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoPresentacion extends Model
{
    protected $table = 'producto_presentaciones';

    protected $fillable = [
        'producto_id', 'nombre', 'unidades', 'es_base', 'precio_costo',
        'precio_menor', 'precio_mayor', 'precio_distribuidor', 'nombre_popular', 'estado',
    ];

    protected $casts = [
        'unidades' => 'float', 'es_base' => 'boolean',
        'precio_costo' => 'float', 'precio_menor' => 'float', 'precio_mayor' => 'float', 'precio_distribuidor' => 'float',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
