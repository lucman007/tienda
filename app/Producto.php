<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
	protected $table='productos';
	protected $primaryKey='idproducto';
	public $timestamps=false;
	protected $fillable=[
		'cod_producto',
        'idcategoria',
		'nombre',
		'presentacion',
		'precio',
        'moneda',
		'costo',
        'moneda_compra',
		'stock_bajo',
		'unidad_medida',
        'tipo_producto',
        'tipo_cambio',
        'imagen',
        'eliminado',
        'discounts'
	];

	public function inventario(){
		return $this->hasMany(Inventario::class,'idproducto')->orderby('idinventario','desc');
	}

	public function categoria(){
		return $this->belongsTo(Categoria::class,'idcategoria','idcategoria');
	}

    public function descuento(){
        return $this->hasMany(Descuento::class,'idproducto');
    }

}
