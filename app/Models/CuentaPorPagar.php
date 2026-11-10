<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaPorPagar extends Model
{
    protected $table = 'cuentas_por_pagar';

    protected $fillable = [
        'tienda_id', 'proveedor_id', 'compra_id', 'numero_documento', 'fecha_emision', 'fecha_vencimiento', 'monto_total', 'monto_pagado', 'saldo', 'estado', 'ultima_alerta_at', 'observaciones',
    ];

    protected $casts = [
        'monto_total' => 'float',
        'monto_pagado' => 'float',
        'saldo' => 'float',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'ultima_alerta_at' => 'datetime',
    ];

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(CxpPago::class, 'cuenta_por_pagar_id');
    }

    public function getEstadoEfectivoAttribute(): string
    {
        if (in_array($this->estado, ['pagada', 'anulada'])) {
            return $this->estado;
        }

        return $this->fecha_vencimiento && $this->fecha_vencimiento->lt(today()) ? 'vencida' : $this->estado;
    }

    public function getDiasVencidoAttribute(): int
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->lt(today())
            ? (int) abs($this->fecha_vencimiento->diffInDays(today()))
            : 0;
    }
}
