<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'ta_permiso';
    protected $primaryKey = 'id_permiso';

    public $timestamps = false;

    protected $fillable = [
        'id_accion',
        'id_rol'
    ];

    // Relación: Pertenece a una Acción
    public function accion()
    {
        return $this->belongsTo(Accion::class, 'id_accion');
    }

    // Relación: Pertenece a un Rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
}
