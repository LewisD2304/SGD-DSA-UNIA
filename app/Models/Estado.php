<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'ta_estado';
    protected $primaryKey = 'id_estado';

    public $timestamps = false; //Sin auditoria

    protected $fillable = [
        'nombre_estado',
        'accion_estado'
    ];

    // Opcional: Transiciones que SALEN de este estado
    public function transicionesSalientes()
    {
        return $this->hasMany(Transicion::class, 'id_estado_actual_transicion');
    }

    // Opcional: Transiciones que LLEGAN a este estado
    public function transicionesEntrantes()
    {
        return $this->hasMany(Transicion::class, 'id_estado_siguiente_transicion');
    }

}
