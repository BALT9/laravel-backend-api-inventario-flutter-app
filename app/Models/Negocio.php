<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Negocio extends Model
{
    use HasFactory;

    protected $table = 'negocios';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'logo',
        'estado'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class);
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class);
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

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }

    public function notas()
    {
        return $this->hasMany(Nota::class);
    }
}
