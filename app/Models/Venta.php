<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{

    protected $fillable = [

        'user_id',
        'sucursal_id',
        'fecha',
        'total',
        'observacion'

    ];


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
        return $this->hasMany(VentaDetalle::class);
    }
}
