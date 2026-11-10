<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ColaCorreo extends Model
{
    protected $table = 'cola_correos';

    protected $fillable = [
        'tienda_id', 'automatizacion_id', 'destinatario', 'asunto', 'plantilla', 'datos', 'cuerpo', 'estado', 'intentos', 'error', 'programado_at', 'enviado_at',
    ];

    protected $casts = [
        'programado_at' => 'datetime',
        'enviado_at' => 'datetime',
        'datos' => 'array',
    ];

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }
}
