<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    const USUARIO_VERIFICADO = true;
    const USUARIO_NO_VERIFICADO = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'verification_token', 'email_verified_at', 'age'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'verification_token',
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

    /**
     * measures
     *
     * @return void
     */
    public function measures()
    {
        return $this->hasMany('App\Measure');
    }
}
