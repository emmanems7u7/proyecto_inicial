<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tienda extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id', 'codigo', 'nombre', 'direccion', 'telefono', 'email',
        'ciudad_id', 'es_matriz', 'estado',
    ];

    protected $casts = ['es_matriz' => 'boolean'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'ciudad_id');
    }

    public function almacenes(): HasMany
    {
        return $this->hasMany(Almacen::class);
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_tienda', 'tienda_id', 'user_id')
            ->withPivot('estado')
            ->withTimestamps();
    }
}
