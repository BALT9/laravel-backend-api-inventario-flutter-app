<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{

    protected $fillable = [

        'categoria_id',
        'sucursal_id',
        'codigo',
        'nombre',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'stock',
        'stock_minimo'

    ];


    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }


    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }


    public function compraDetalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }


    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }


    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}
