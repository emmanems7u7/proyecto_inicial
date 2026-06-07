<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPersonalizacion extends Model
{

    protected $fillable = [
        'user_id',
        'sidebar_color',
        'sidebar_type',
        'dark_mode'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
