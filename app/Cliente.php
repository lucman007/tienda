<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
	protected $table='cliente';
	protected $primaryKey='idcliente';
	public $timestamps=false;
	protected $fillable=[
		'cod_cliente',
		'num_documento',
		'tipo_documento',
        'tocken',
        'cuentas',
		'eliminado'
	];

	public function persona()
	{
		return $this->hasOne(Persona::class,'idpersona','idcliente');
	}

    public function venta()
    {
        return $this->hasOne(Venta::class,'idcliente','idcliente');
    }
}
