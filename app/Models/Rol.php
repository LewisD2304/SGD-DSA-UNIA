<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{

    protected $table = 'ta_rol';
    protected $primaryKey = 'id_rol';

    //cuando no tienes los campos
    #Dos campos de auditoria modificacion y creacion
    public $timestamps = false;


    protected $fillable = [
        'nombre_rol',
        'estado_rol',
        'descripcion_rol'
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol');
    }









}
