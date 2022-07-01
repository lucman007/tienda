<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table='almacen';
    protected $primaryKey='idalmacen';
    public $timestamps=false;
    protected $fillable=[
        'codigo',
        'nombre',
        'fecha',
        'eliminado'
    ];

    public function ubicacion(){
        return $this->hasMany(Ubicacion::class,'idalmacen');
    }

    public function productos(){
        return $this->belongsToMany(Producto::class,'almacen_productos','idalmacen','idproducto')
            ->as('almacen_productos')
            ->withPivot('idubicacion');
    }

}
