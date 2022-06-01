<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Gastos extends Model
{
    protected $table='gastos';
    protected $primaryKey='idgasto';
    public $timestamps=false;
    protected $fillable=[
        'idempleado',
        'idcaja',
        'monto',
        'descripcion',
        'tipo',
        'tipo_egreso',
        'fecha',
        'tipo_pago_empleado',
        'mes_pago_empleado',
        'tipo_comprobante',
        'num_comprobante'
    ];

    public function empleado(){
        return $this->hasOne(Persona::class,'idpersona','idempleado');
    }

    public function cajero(){
        return $this->hasOne(Persona::class,'idpersona','idcaja');
    }

}
