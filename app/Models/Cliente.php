<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $fillable = ['empresa_id', 'tipo_documento_id', 'numero_documento', 'complemento', 'nombre', 'email', 'telefono', 'direccion', 'ciudad_id', 'limite_credito', 'dias_credito', 'observaciones', 'estado'];

    protected $casts = ['limite_credito' => 'float'];

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'tipo_documento_id');
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'ciudad_id');
    }
}
