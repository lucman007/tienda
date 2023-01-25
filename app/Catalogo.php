<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    protected $table = 'catalogos';
    protected $primaryKey = 'idcatalogo';
    public $timestamps = false;
    protected $fillable = [
        'titulo',
        'subtitulo',
        'imagen_portada',
        'precios',
        'fecha',
        'footer',
        'eliminado',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'catalogo_detalle', 'idcatalogo', 'idproducto')
            ->as('detalle')
            ->withPivot('num_item');
    }
}
