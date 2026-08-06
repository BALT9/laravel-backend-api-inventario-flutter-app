<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{

    protected $fillable = [

        'negocio_id',
        'producto_id',
        'user_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_actual'

    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }


    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }


    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
