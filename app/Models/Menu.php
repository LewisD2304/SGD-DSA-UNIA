<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{

    protected $table = 'ta_menu';
    protected $primaryKey = 'id_menu';
    public $timestamps = false;

    protected $fillable = [
        'nombre_menu',
        'ruta_menu',
        'estado_menu'
    ];

    protected $casts = [
        'estado_menu' => EstadoEnum::class,
    ];

    public function acciones()
    {
        return $this->hasMany(Accion::class, 'id_menu');
    }

    public function hijos()
    {
        return $this->hasMany(Menu::class, 'id_padre')
            ->with([
                'acciones.tipoAccion',
                'acciones.permisos',
                'hijos' // <- aquí ocurre la recursividad
            ]);
    }

    public function scopeBuscar($query, $buscar)
    {
        if ($buscar == null ) {
            return $query;
        }

        return $query->where(function ($query) use ($buscar) {
            $query->where('nombre_menu', 'LIKE', "%$buscar%")
                ->orWhere('ruta_menu', 'LIKE', "%$buscar%");
        });
    }

    // Scope para filtrar por estado
    public function scopeEstado($query, $estado)
    {
        if ($estado == null) {
            return $query;
        }

        return $query->where('estado_menu', $estado);
    }
}
