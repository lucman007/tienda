<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    protected $table='descuentos';
    protected $primaryKey='iddescuento';
    public $timestamps=false;
    protected $fillable=[
        'idproducto',
        'cantidad_min',
        'monto_desc'
    ];
}
