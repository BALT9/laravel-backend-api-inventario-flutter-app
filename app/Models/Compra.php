<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{

    protected $fillable = [

        'negocio_id',
        'user_id',
        'sucursal_id',
        'fecha',
        'total',
        'observacion'

    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }


    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }
}
