<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono'
    ];


    public function productos()
    {
        return $this->hasMany(Producto::class);
    }


    public function compras()
    {
        return $this->hasMany(Compra::class);
    }


    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
