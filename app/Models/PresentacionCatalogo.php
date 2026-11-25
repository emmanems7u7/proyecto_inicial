<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresentacionCatalogo extends Model
{
    protected $table = 'presentaciones_catalogo';

    protected $fillable = ['empresa_id', 'nombre', 'unidades', 'estado'];

    protected $casts = ['unidades' => 'float'];
}
