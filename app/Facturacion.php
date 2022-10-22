<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Facturacion extends Model
{
    protected $table='facturacion';
    protected $primaryKey='idventa';
    public $timestamps=false;
    protected $fillable=[
        'codigo_tipo_documento',
        'codigo_tipo_factura',
        'serie',
        'correlativo',
        'codigo_moneda',
        'total_exoneradas',
        'total_inafectas',
        'total_gravadas',
        'total_gratuitas',
        'total_descuentos',
        'igv',
        'isc',
        'ivap',
        'estado',
        'valor_venta_bruto',
        'oc_relacionada',
        'guia_relacionada',
        'guia_datos_adicionales',
        'guia_fisica',
        'estado_guia',
        'tipo_nota_electronica',
        'descripcion_nota',
        'num_doc_relacionado',
        'tipo_doc_relacionado',
        'codigo_cargos_descuentos',
        'porcentaje_descuento_global',
        'descuento_global',
        'base_descuento_global',
        'motivo_baja',
        'idresumen',
        'retencion',
        'tipo_detraccion'
    ];
}
