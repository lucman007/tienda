<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Resumen extends Model
{
    protected $table='resumen';
    protected $primaryKey='idresumen';
    public $timestamps=false;
    protected $fillable=[
        'tipo',
        'num_ticket',
        'lote',
        'lote_baja',
        'estado'
    ];
}
