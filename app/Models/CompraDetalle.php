<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{

    public $timestamps = false;


    protected $fillable = [

        'compra_id',
        'producto_id',
        'cantidad',
        'precio',
        'subtotal'

    ];


    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }


    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
