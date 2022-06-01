<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class VentaPago extends Model
{
    protected $table='ventas_pago';
    protected $primaryKey='idpago';
    public $timestamps=false;
    protected $fillable=[
        'idventa',
        'tipo',
        'monto'
    ];

    public function venta(){
        return $this->hasMany(Venta::class,'idventa','idventa');
    }
}
