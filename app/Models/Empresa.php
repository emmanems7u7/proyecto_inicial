<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'razon_social', 'nombre_comercial', 'nit', 'email', 'telefono',
        'direccion', 'logo', 'ciudad_id', 'estado',
    ];

    public function tiendas(): HasMany
    {
        return $this->hasMany(Tienda::class);
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'ciudad_id');
    }

    public function getNombreAttribute(): string
    {
        return $this->nombre_comercial ?: $this->razon_social;
    }
}
