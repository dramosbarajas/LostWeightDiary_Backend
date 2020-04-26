<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Measure extends Model
{


    protected $fillable = [
        'estatura', 'peso', 'cadera', 'cintura', 'pecho', 'brazo', 'pierna', 'cuello', 'user_id'
    ];
    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
