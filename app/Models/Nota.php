<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{

    protected $fillable = [
        'negocio_id',
        'user_id',
        'titulo',
        'descripcion'
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
