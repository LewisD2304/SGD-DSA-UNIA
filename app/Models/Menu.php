<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'ta_menu';
    protected $primaryKey = 'id_menu';

    public $timestamps = false; // Sin auditoría

    protected $fillable = [
        'nombre_menu',
        'ruta_menu',
        'estado_menu'
    ];

    // Relación: Un Menú tiene muchas Acciones (botones/enlaces dentro de él)
    public function acciones()
    {
        return $this->hasMany(Accion::class, 'id_menu');
    }
}
