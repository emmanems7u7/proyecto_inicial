<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'secciones';
    protected $fillable = ['titulo', 'icono', 'posicion'];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}
