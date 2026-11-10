<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [
        'tienda_id', 'almacen_id', 'cliente_id', 'vendedor_id', 'caja_sesion_id', 'cotizacion_id', 'numero', 'numero_factura', 'fecha', 'tipo_pago', 'subtotal', 'descuento', 'total', 'pagado', 'saldo', 'estado', 'anulada_at', 'anulada_por', 'motivo_anulacion', 'observaciones',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'descuento' => 'float',
        'total' => 'float',
        'pagado' => 'float',
        'saldo' => 'float',
        'fecha' => 'datetime',
        'anulada_at' => 'datetime',
    ];

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'caja_sesion_id');
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(DevolucionVenta::class);
    }

    public function cuentaPorCobrar(): HasOne
    {
        return $this->hasOne(CuentaPorCobrar::class);
    }
}
