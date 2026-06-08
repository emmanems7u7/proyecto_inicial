<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    protected $fillable = [
        'api_event_id',
        'equipo_local_id',
        'equipo_visitante_id',
        'temporada',
        'liga',
        'ronda',
        'fecha',
        'hora',
        'estadio',
        'pais',
        'goles_local',
        'goles_visitante',
        'estado',
        'thumbnail',
        'poster',
    ];

    public function local()
    {
        return $this->belongsTo(Equipo::class, 'equipo_local_id');
    }

    public function visitante()
    {
        return $this->belongsTo(Equipo::class, 'equipo_visitante_id');
    }
    public function apuestas()
    {
        return $this->hasMany(Apuesta::class);
    }
}
