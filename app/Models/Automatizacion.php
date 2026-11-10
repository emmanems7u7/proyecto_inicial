<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Automatizacion extends Model
{
    protected $table = 'automatizaciones';

    protected $fillable = [
        'tienda_id', 'codigo', 'nombre', 'descripcion', 'activo', 'frecuencia', 'hora_ejecucion', 'parametros', 'ultima_ejecucion_at',
    ];

    protected $casts = [
        'ultima_ejecucion_at' => 'datetime',
        'activo' => 'boolean',
        'parametros' => 'array',
    ];

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AutomatizacionLog::class);
    }

    public function parametro(string $clave, $defecto = null)
    {
        $valor = data_get($this->parametros, $clave);

        return ($valor === null || $valor === '') ? $defecto : $valor;
    }
}
