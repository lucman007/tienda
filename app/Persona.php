<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
	protected $table='persona';
	protected $primaryKey='idpersona';
	public $timestamps=false;
	protected $fillable=[
		'nombre',
		'apellidos',
		'direccion',
		'telefono',
		'correo',
		'ciudad'
	];

	public function cliente()
	{
		return $this->hasOne(Cliente::class,'idcliente');
	}

    public function proveedor()
    {
        return $this->hasOne(Proveedor::class,'idproveedor');
    }

	public function trabajador()
	{
		return $this->hasOne(Trabajador::class,'idempleado');
	}
}
