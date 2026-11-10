<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevolucionCompra extends Model
{
    protected $table = 'devoluciones_compra';

    protected $fillable = [
        'tienda_id', 'compra_id', 'user_id', 'numero', 'fecha', 'total', 'motivo', 'estado',
    ];

    protected $casts = [
        'total' => 'float',
        'fecha' => 'date',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DevolucionCompraDetalle::class);
    }
}
