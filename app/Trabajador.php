<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
	protected $table='empleado';
	protected $primaryKey='idempleado';
	public $timestamps=false;
	protected $fillable=[
		'fecha_ingreso',
        'dia_pago',
		'ciclo_pago',
		'sueldo',
		'dni',
		'es_usuario',
		'usuario',
		'password',
		'acceso',
        'cargo',
		'eliminado'
	];

    protected $hidden = [
        'password', 'remember_token', 'usuario', 'es_usuario', 'acceso'
    ];


    /*public function persona()
    {
        return $this->belongsTo(Persona::class,'idpersona');
    }*/

	public function persona()	{
		return $this->hasOne(Persona::class,'idpersona','idempleado');
	}
}
