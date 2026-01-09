<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'ta_area';
    protected $primaryKey = 'id_area';

    public $timestamps = false; //Sin auditoria

    protected $fillable = [
        'nombre_area',
        'siglas_area'
    ];

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'id_area', 'id_area');
    }

    public function personas()
    {
        return $this->hasMany(Persona::class, 'id_area', 'id_area');
    }
}
