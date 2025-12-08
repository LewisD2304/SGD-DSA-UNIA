<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{

    protected $table = 'ta_rol';
    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre_rol',
        'estado_rol',
        'descripcion_rol'
    ];

    protected $casts = [
        'estado_rol' => EstadoEnum::class,
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol');
    }

    // Relación: Un Rol tiene muchos Permisos
    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'id_rol');
    }

    // Scope para búsqueda por coincidencia
    public function scopeBuscar($query, $buscar)
    {
        if (empty($buscar)) {
            return $query;
    }

        return $query->where('nombre_rol', 'LIKE', "%{$buscar}%");
    }

    // Scope para filtrar por estado
    public function scopeEstado($query, $estado)
    {
        if ($estado == null) {
            return $query;
        }

        return $query->where('estado_rol', $estado);
    }

    // Scope para limitar la búsqueda
    public function scopeLimite($query, $limite)
    {
        if ($limite == null) {
            return $query;
        }

        return $query->limit($limite);
    }

    public $timestamps = false;

}
