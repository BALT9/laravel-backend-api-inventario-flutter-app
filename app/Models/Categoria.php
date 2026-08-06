<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{

    protected $table = 'categorias';

    protected $fillable = [
        'negocio_id',
        'nombre',
        'descripcion'
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
