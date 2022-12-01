<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Guia extends Model
{
    protected $table='guia';
    protected $primaryKey='idguia';
    public $timestamps=false;
    protected $fillable=[
        'correlativo',
        'idempleado',
        'idcliente',
        'idventa',
        'fecha_emision',
        'nota',
        'estado',
        'ticket',
        'response',
        'guia_datos_adicionales',
    ];

    public function productos(){
        return $this->belongsToMany(Producto::class,'guia_detalle','idguia','idproducto')
            ->as('detalle')
            ->withPivot('num_item', 'cantidad', 'descripcion','precio');
    }

    public function persona(){
        return $this->hasOne(Persona::class,'idpersona','idcliente');
    }

    public function cliente(){
        return $this->hasOne(Cliente::class,'idcliente','idcliente');
    }
}
