<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accion extends Model
{
    protected $table = 'ta_accion';
    protected $primaryKey = 'id_accion';

    public $timestamps = false;

    protected $fillable = [
        'nombre_accion',
        'id_menu'
    ];

    // Relación: Pertenece a un Menú
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    // Relación: Una Acción está en muchos Permisos
    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'id_accion');
    }
}
