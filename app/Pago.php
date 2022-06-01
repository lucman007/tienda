<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table='pagos';
    protected $primaryKey='idpago';
    public $timestamps=false;
    protected $fillable=[
        'idventa',
        'tipo',
        'monto',
        'fecha'
    ];

    public function venta(){
        return $this->hasMany(Venta::class,'idventa','idventa');
    }
}
