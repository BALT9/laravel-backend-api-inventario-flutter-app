<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'negocio_id',
        'nombre',
        'role',
        'email',
        'password',
        'estado'
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


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

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }
}
