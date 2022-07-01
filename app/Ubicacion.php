<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table='almacen_ubicacion';
    protected $primaryKey='idubicacion';
    public $timestamps=false;
    protected $fillable=[
        'idalmacen',
        'codigo',
        'nombre',
        'descripcion',
        'eliminado'
    ];

    public function almacen(){
        return $this->hasOne(Almacen::class,'idalmacen');
    }
}
