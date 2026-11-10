<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutomatizacionLog extends Model
{
    protected $table = 'automatizacion_logs';

    protected $fillable = [
        'automatizacion_id', 'tienda_id', 'estado', 'mensaje', 'detalle', 'iniciada_at', 'finalizada_at',
    ];

    protected $casts = [
        'iniciada_at' => 'datetime',
        'finalizada_at' => 'datetime',
        'detalle' => 'array',
    ];

    public function automatizacion(): BelongsTo
    {
        return $this->belongsTo(Automatizacion::class);
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }
}
