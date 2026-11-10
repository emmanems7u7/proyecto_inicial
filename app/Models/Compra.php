<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use SoftDeletes;

    protected $table = 'compras';

    protected $fillable = [
        'tienda_id', 'almacen_id', 'proveedor_id', 'orden_compra_id', 'user_id', 'numero', 'numero_documento_proveedor', 'fecha', 'tipo_pago', 'subtotal', 'descuento', 'total', 'pagado', 'saldo', 'estado', 'observaciones',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'descuento' => 'float',
        'total' => 'float',
        'pagado' => 'float',
        'saldo' => 'float',
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

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(DevolucionCompra::class);
    }

    public function cuentaPorPagar(): HasOne
    {
        return $this->hasOne(CuentaPorPagar::class);
    }
}
