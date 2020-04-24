<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    const USUARIO_VERIFICADO = true;
    const USUARIO_NO_VERIFICADO = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'tokenVerified', 'email_verified_at', 'age'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'tokenVerified',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Funcion para generar el token 
     * 
     */

    public static function generateToken()
    {
        return str_random(64);
    }

    /**
     * Utilizamos los mutators de Laravel para el atributo name pasarlo siempre a minusculas
     * 
     */
    public function setNameAttribute($valor)
    {
        $this->attributes['name'] = strtolower($valor);
    }

    /**
     * Utilizamos los mutators para cuando recuperemos el nombre lo pintemos con la primera letra en mayuscula. 
     * 
     */
    public function getNameAttribute($valor)
    {
        return ucwords($valor);
    }

    /**
     * Utilizamos los mutators para almacenar el email siempre en minusculas.
     * 
     */
    public function setEmailAttribute($valor)
    {
        $this->attributes['email'] = strtolower($valor);
    }


    /**
     * esVerificado
     * Comprueba si un usuario ha verificado su cuenta
     * @return void
     */
    public function esVerificado()
    {
        return $this->email_verified_at !=  null;
    }
}
