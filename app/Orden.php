<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
	protected $table='orden';
	protected $primaryKey='idorden';
	public $timestamps=false;
	protected $fillable=[
		'idempleado',
		'idcliente',
		'fecha',
		'observaciones',
        'total',
        'comprobante',
		'estado',
        'moneda',
        'igv_incluido',
        'idventa',
        'datos_entrega'
	];

	public function productos(){
		return $this->belongsToMany(Producto::class,'orden_detalle','idorden','idproducto')
		            ->as('detalle')
		            ->withPivot('num_item', 'cantidad', 'monto', 'descripcion','descuento','items_kit','serie');
	}

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'idempleado', 'idempleado');
    }

    public function persona()
    {
        return $this->hasOne(Persona::class, 'idpersona', 'idcliente');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'idcliente', 'idcliente');
    }

    public function vendedor()
    {
        return $this->hasOne(Persona::class, 'idpersona', 'idempleado');
    }
}
