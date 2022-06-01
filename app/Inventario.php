<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
	protected $table='inventario';
	protected $primaryKey='idinventario';
	public $timestamps=false;
	protected $fillable=[
		'idproducto',
		'idempleado',
		'fecha',
		'cantidad',
        'costo',
        'saldo',
        'moneda',
        'tipo_cambio',
		'operacion',
		'descripcion'
	];

	public function producto(){
		return $this->belongsTo(Producto::class);
	}

    public function empleado(){
        return $this->hasOne(Persona::class,'idpersona','idempleado');
    }
}
