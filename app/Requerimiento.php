<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Requerimiento extends Model
{
	protected $table='requerimiento';
	protected $primaryKey='idrequerimiento';
	public $timestamps=false;
	protected $fillable=[
		'idempleado',
		'idproveedor',
        'correlativo',
		'fecha_requerimiento',
		'fecha_recepcion',
		'total_compra',
		'num_comprobante',
		'estado',
		'eliminado'
	];

    public function productos(){
        return $this->belongsToMany(Producto::class,'requerimiento_detalle','idrequerimiento','idproducto')
            ->as('detalle')
            ->withPivot('num_item', 'cantidad', 'monto', 'descripcion','cantidad_recepcion', 'monto_recepcion','total_recepcion','descuento');
    }

    public function proveedor(){
        return $this->hasOne(Proveedor::class,'idproveedor','idproveedor');
    }

}
