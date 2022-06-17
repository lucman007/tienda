<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table='caja';
    protected $primaryKey='idcaja';
    public $timestamps=false;
    protected $fillable=[
        'idempleado',
        'fecha_a',
        'observacion_a',
        'apertura',
        'efectivo_teorico',
        'efectivo_real',
        'efectivo',
        'tarjeta',
        'devoluciones',
        'credito',
        'extras',
        'gastos',
        'observacion_c',
        'fecha_c',
        'descuadre',
        'estado',
        'turno'
    ];



    public function empleado()
    {
        return $this->belongsTo(Persona::class, 'idempleado', 'idpersona');
    }

}
