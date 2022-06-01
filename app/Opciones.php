<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Opciones extends Model
{
    protected $table='opciones';
    protected $primaryKey='idopcion';
    public $timestamps=false;
    protected $fillable=[
        'fecha',
        'nombre_opcion',
        'valor',
        'valor_json',
        'tipo_egreso'
    ];
}
