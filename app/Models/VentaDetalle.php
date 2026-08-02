<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{

    public $timestamps = false;


    protected $fillable = [

        'venta_id',
        'producto_id',
        'cantidad',
        'precio',
        'subtotal'

    ];


    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }


    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
