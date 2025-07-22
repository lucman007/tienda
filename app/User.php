<?php

namespace sysfact;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class User extends Authenticatable
{
    use Notifiable,HasRoles;

	protected $table='empleado';
	protected $primaryKey='idempleado';
	public $timestamps=false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dni', 'usuario', 'password','session_id','notification_checked_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','session_id'
    ];

    public function persona()	{
        return $this->hasOne(Persona::class,'idpersona','idempleado');
    }

}
