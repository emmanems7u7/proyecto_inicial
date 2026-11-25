<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Almacen extends Model
{
    use SoftDeletes;

    protected $table = 'almacenes';

    public const TIPOS = [
        'principal' => ['Principal', 'Recibe las compras y abastece las ventas', 'success'],
        'exhibicion' => ['Exhibición', 'Productos a la vista del cliente', 'info'],
        'merma' => ['Merma', 'Dañados o vencidos, no se venden', 'danger'],
    ];

    protected $fillable = ['tienda_id', 'codigo', 'nombre', 'direccion', 'es_principal', 'estado', 'tipo', 'encargado_id', 'ubicaciones'];

    protected $casts = ['es_principal' => 'boolean'];

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function encargado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encargado_id');
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return ($this->tienda?->nombre ? $this->tienda->nombre . ' — ' : '') . $this->nombre;
    }
}
