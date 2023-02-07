<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    protected $table = 'presupuesto';
    protected $primaryKey = 'idpresupuesto';
    public $timestamps = false;
    protected $fillable = [
        'idempleado',
        'idcliente',
        'fecha',
        'presupuesto',
        'descuento',
        'porcentaje_descuento',
        'tipo_descuento',
        'moneda',
        'ocultar_impuestos',
        'ocultar_precios',
        'observaciones',
        'atencion',
        'validez',
        'condicion_pago',
        'tiempo_entrega',
        'garantia',
        'impuesto',
        'lugar_entrega',
        'contacto',
        'telefonos',
        'igv_incluido',
        'exportacion',
        'datos_adicionales',
        'incoterm',
        'flete',
        'seguro',
        'referencia',
        'eliminado'
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'presupuesto_detalle', 'idpresupuesto', 'idproducto')
            ->as('detalle')
            ->withPivot('num_item', 'cantidad', 'monto', 'descripcion', 'descuento', 'porcentaje_descuento','tipo_descuento','descuento_por_und', 'producto_nombre');
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'idpersona', 'idempleado');
    }

    public function empleado()
    {
        return $this->hasOne(Persona::class, 'idpersona', 'idempleado');
    }

    public function persona()
    {
        return $this->hasOne(Persona::class, 'idpersona', 'idcliente');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'idcliente', 'idcliente');
    }
}
