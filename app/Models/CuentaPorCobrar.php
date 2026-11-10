<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaPorCobrar extends Model
{
    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'tienda_id', 'cliente_id', 'venta_id', 'numero_documento', 'fecha_emision', 'fecha_vencimiento', 'monto_total', 'monto_pagado', 'saldo', 'estado', 'recordatorios_enviados', 'ultimo_recordatorio_at', 'observaciones',
    ];

    protected $casts = [
        'monto_total' => 'float',
        'monto_pagado' => 'float',
        'saldo' => 'float',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'ultimo_recordatorio_at' => 'datetime',
    ];

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(CxcPago::class, 'cuenta_por_cobrar_id');
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
