<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    public const TIPOS = [
        'entrada' => 'Entrada',
        'salida' => 'Salida',
        'ajuste' => 'Ajuste',
        'venta' => 'Venta',
        'compra' => 'Compra',
        'devolucion_venta' => 'Devolución de venta',
        'devolucion_compra' => 'Devolución de compra',
        'transferencia_entrada' => 'Transferencia (entrada)',
        'transferencia_salida' => 'Transferencia (salida)',
        'inventario_fisico' => 'Inventario físico',
    ];

    protected $fillable = [
        'tienda_id', 'almacen_id', 'producto_id', 'lote_id', 'tipo', 'cantidad',
        'saldo_anterior', 'saldo_posterior', 'costo_unitario', 'motivo_id',
        'referencia_type', 'referencia_id', 'user_id', 'observacion', 'fecha',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'saldo_anterior' => 'float',
        'saldo_posterior' => 'float',
        'costo_unitario' => 'float',
        'fecha' => 'datetime',
    ];

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function motivo(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'motivo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTipoNombreAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }
}
