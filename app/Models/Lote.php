<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lote extends Model
{
    /** Días de anticipación para considerar un lote "por vencer". */
    public const DIAS_ALERTA = 30;

    protected $fillable = [
        'producto_id', 'almacen_id', 'numero_lote', 'fecha_ingreso', 'fecha_vencimiento',
        'cantidad_inicial', 'cantidad_actual', 'costo_unitario', 'estado',
        'alerta_vencimiento_enviada_at',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_vencimiento' => 'date',
        'cantidad_inicial' => 'float',
        'cantidad_actual' => 'float',
        'costo_unitario' => 'float',
        'alerta_vencimiento_enviada_at' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function getDiasParaVencerAttribute(): ?int
    {
        if (! $this->fecha_vencimiento) {
            return null;
        }

        return (int) today()->diffInDays($this->fecha_vencimiento, false);
    }

    /** agotado | sin_vencimiento | vencido | por_vencer | vigente */
    public function getEstadoVencimientoAttribute(): string
    {
        if ($this->cantidad_actual <= 0) {
            return 'agotado';
        }
        $dias = $this->dias_para_vencer;
        if ($dias === null) {
            return 'sin_vencimiento';
        }
        if ($dias < 0) {
            return 'vencido';
        }

        return $dias <= self::DIAS_ALERTA ? 'por_vencer' : 'vigente';
    }

    public function scopeEstadoVencimiento(Builder $query, ?string $estado): Builder
    {
        $hoy = today()->toDateString();
        $limite = today()->addDays(self::DIAS_ALERTA)->toDateString();

        return match ($estado) {
            'agotado' => $query->where('cantidad_actual', '<=', 0),
            'vencido' => $query->where('cantidad_actual', '>', 0)->whereDate('fecha_vencimiento', '<', $hoy),
            'por_vencer' => $query->where('cantidad_actual', '>', 0)
                ->whereDate('fecha_vencimiento', '>=', $hoy)
                ->whereDate('fecha_vencimiento', '<=', $limite),
            'vigente' => $query->where('cantidad_actual', '>', 0)
                ->where(fn ($q) => $q->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '>', $limite)),
            default => $query,
        };
    }
}
