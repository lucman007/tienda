<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $table='mesa';
    protected $primaryKey='idmesa';
    public $timestamps=false;
    protected $fillable=[
        'numero',
        'piso',
        'estado',
        'observacion',
    ];

    public function orden()
    {
        return $this->hasOne(Orden::class,'idmesa','idmesa');
    }
}
