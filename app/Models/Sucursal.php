<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'negocio_id',
        'nombre',
        'direccion',
        'telefono'
    ];


    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

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
