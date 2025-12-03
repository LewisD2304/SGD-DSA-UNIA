<?php

namespace App\Models;

use App\Models\Catalogo;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Eloquent\Model;

class Accion extends Model
{
    protected $table = 'ta_accion';
    protected $primaryKey = 'id_accion';

    public $timestamps = false;

    protected $fillable = [
        'accion_catalogo',
        'id_menu'
    ];

    // Relación: Pertenece a un Menú
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }
    // Relacion de tipo de acción con catálogo
    public function tipoAccion()
    {
        return $this->belongsTo(Catalogo::class, 'accion_catalogo');
    }

    // Relación: Una Acción está en muchos Permisos
    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'id_accion');
    }

    // Relación de muchos a muchos con la tabla ta_rol
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'ta_permiso', 'id_accion', 'id_rol');
    }
}
