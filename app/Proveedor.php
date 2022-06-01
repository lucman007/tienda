<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
	protected $table='proveedores';
	protected $primaryKey='idproveedor';
	public $timestamps=false;
	protected $fillable=[
		'codigo',
		'num_documento',
		'tipo_documento',
		'titular',
		'telefono_2',
		'web',
		'contacto',
		'observaciones',
		'eliminado'
	];

	public function persona()
	{
		return $this->hasOne(Persona::class,'idpersona','idproveedor');
	}
}
