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
        'discounts',
        'marca',
        'modelo',
        'param_1',
        'param_2',
        'param_3',
        'param_4',
        'param_5',
        'items_kit',
	];

    public function almacen(){
        return $this->belongsToMany(Almacen::class,'almacen_productos','idproducto','idalmacen')
            ->as('almacen_productos')
            ->withPivot('idubicacion');
    }

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
