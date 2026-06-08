<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apuesta extends Model
{
    protected $fillable = [
        'partido_id',
        'matricula',
        'nombre_completo',
        'monto',
        'prediccion_local',
        'prediccion_visitante',
        'codigo_apuesta',
        'comprobante',
        'estado_pago',
        'mensaje_validador',
        'estado_apuesta'
    ];


    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

}
