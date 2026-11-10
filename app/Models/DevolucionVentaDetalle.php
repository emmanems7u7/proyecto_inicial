<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevolucionVentaDetalle extends Model
{
    protected $table = 'devolucion_venta_detalles';

    protected $fillable = [
        'devolucion_venta_id', 'producto_id', 'lote_id', 'cantidad', 'precio_unitario', 'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'precio_unitario' => 'float',
        'subtotal' => 'float',
    ];

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionVenta::class, 'devolucion_venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }
}
