<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CajaSesion extends Model
{
    protected $table = 'caja_sesiones';

    protected $fillable = [
        'caja_id', 'user_apertura_id', 'user_cierre_id', 'fecha_apertura', 'fecha_cierre', 'monto_apertura', 'total_ingresos', 'total_egresos', 'monto_esperado', 'monto_real', 'diferencia', 'tipo_cierre', 'estado', 'observaciones',
    ];

    protected $casts = [
        'monto_apertura' => 'float',
        'total_ingresos' => 'float',
        'total_egresos' => 'float',
        'monto_esperado' => 'float',
        'monto_real' => 'float',
        'diferencia' => 'float',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function usuarioApertura(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_apertura_id');
    }

    public function usuarioCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_cierre_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class);
    }
}
