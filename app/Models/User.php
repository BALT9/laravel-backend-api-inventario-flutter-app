<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;


    protected $fillable = [
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
}
