<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transferencia extends Model
{
    protected $table = 'transferencias';

    protected $fillable = [
        'empresa_id', 'numero', 'tipo', 'tienda_origen_id', 'almacen_origen_id', 'tienda_destino_id', 'almacen_destino_id', 'user_envia_id', 'user_recibe_id', 'fecha', 'fecha_recepcion', 'estado', 'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_recepcion' => 'datetime',
    ];

    public function tiendaOrigen(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'tienda_origen_id');
    }

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function tiendaDestino(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'tienda_destino_id');
    }

    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function usuarioEnvia(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_envia_id');
    }

    public function usuarioRecibe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_recibe_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(TransferenciaDetalle::class);
    }
}
