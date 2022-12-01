<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Produccion extends Model
{
    protected $table = 'produccion';
    protected $primaryKey = 'idproduccion';
    public $timestamps = false;
    protected $fillable = [
        'correlativo',
        'idempleado',
        'idcliente',
        'fecha_emision',
        'fecha_entrega',
        'prioridad',
        'adjuntos',
        'num_oc',
        'nota',
        'observacion',
        'estado',
        'eliminado'
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'produccion_detalle', 'idproduccion', 'idproducto')
            ->as('detalle')
            ->withPivot('num_item', 'cantidad','descripcion', 'observacion','codigo_fabricacion');
    }

    public function empleado()
    {
        return $this->belongsTo(Trabajador::class, 'idpersona', 'idempleado');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'idcliente', 'idcliente');
    }
}
