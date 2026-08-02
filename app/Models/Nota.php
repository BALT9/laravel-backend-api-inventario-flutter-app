<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{

    protected $fillable = [

        'user_id',
        'titulo',
        'descripcion'

    ];


    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
