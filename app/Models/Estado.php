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

    public function documentos() {
        return $this->hasMany(Documento::class, 'id_estado');
    }

    public function movimientos() {
        return $this->hasMany(Movimiento::class, 'id_estado');
    }

    public function transicionesSalientes() {
        return $this->hasMany(Transicion::class, 'id_estado_actual_transicion');
    }

    public function transicionesEntrantes(){
        return $this->hasMany(Transicion::class, 'id_estado_siguiente_transicion');
    }




}
