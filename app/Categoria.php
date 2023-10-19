<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'idcategoria';
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'eliminado'
    ];

    public function producto()
    {
        return $this->hasMany(Producto::class, 'idcategoria');
    }
}
