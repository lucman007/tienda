<?php

namespace sysfact;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table='notifications';
    protected $primaryKey='id';
    public $timestamps=false;
    protected $fillable=[
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'created_at',
        'updated_at'
    ];
}
