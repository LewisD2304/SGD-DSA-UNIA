<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{

    protected $table = 'ta_persona';

    protected $primaryKey = 'id_persona';

    public $timestamps = false;

    protected $fillable = [
        'nombres_persona',
        'apellidos_persona',
        'dni_persona',
        'celular_persona',
        'correo_persona'
    ];

    public function usuarios()
    {
        return $this->hasOne(Usuario::class, 'id_persona', 'id_persona');
    }
}
