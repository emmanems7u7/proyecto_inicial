<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = ['empresa_id', 'tipo_documento_id', 'numero_documento', 'nombre', 'contacto_nombre', 'email', 'telefono', 'direccion', 'ciudad_id', 'dias_credito', 'observaciones', 'estado'];

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'tipo_documento_id');
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'ciudad_id');
    }
}
