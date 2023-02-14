<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
	protected $table='ventas';
	protected $primaryKey='idventa';
	public $timestamps=false;
	protected $fillable=[
		'idempleado',
		'idcliente',
		'idcajero',
        'idcaja',
		'fecha',
        'fecha_vencimiento',
		'total_venta',
		'tipo_pago',
		'observacion',
		'estado',
        'igv_incluido',
		'eliminado',
        'data_credito',
        'ticket',
        'tipo_cambio',
	];

	public function productos(){
		return $this->belongsToMany(Producto::class,'ventas_detalle','idventa','idproducto')
			->as('detalle')
			->withPivot('num_item', 'cantidad', 'monto', 'porcentaje_descuento','descuento', 'descripcion',
                'producto_nombre','afectacion','subtotal','igv','total','devueltos','items_kit');
	}

	public function persona(){
		return $this->hasOne(Persona::class,'idpersona','idcliente');
	}

    public function facturacion(){
        return $this->hasOne(Facturacion::class,'idventa','idventa');
    }

	public function cliente(){
		return $this->hasOne(Cliente::class,'idcliente','idcliente');
	}

    public function guia(){
        return $this->hasMany(Guia::class,'idventa');
    }

    public function orden(){
        return $this->hasOne(Orden::class,'idventa');
    }

    public  function pago(){
        return $this->hasMany(Pago::class, 'idventa', 'idventa');
    }

    public function empleado(){
        return $this->hasOne(Persona::class,'idpersona', 'idempleado');
    }
    public function caja(){
        return $this->hasOne(Persona::class,'idpersona', 'idcajero');
    }
    public  function inventario(){
        return $this->hasMany(Inventario::class, 'idventa', 'idventa');
    }

}
