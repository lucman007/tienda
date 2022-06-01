<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $table='app_config';
    protected $primaryKey='clave';
    public $timestamps=false;
    public $incrementing = false;
    protected $fillable=[
        'clave',
        'valor'
    ];
}
