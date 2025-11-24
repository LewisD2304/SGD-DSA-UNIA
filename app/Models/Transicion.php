<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transicion extends Model
{
    protected $table = 'ta_transicion';
    protected $primaryKey = 'id_transicion';

    public $timestamps = false;

    protected $fillable = [
        'evento',
        'id_estado_actual_transicion',
        'id_estado_siguiente_transicion'
    ];

    public function estadoActual()
    {
        // belongsTo(Modelo, 'mi_fk', 'id_del_otro')
        return $this->belongsTo(Estado::class, 'id_estado_actual_transicion', 'id_estado');
    }

    // 2. Relación con el Estado de Destino (A dónde voy)
    public function estadoSiguiente()
    {
        return $this->belongsTo(Estado::class, 'id_estado_siguiente_transicion', 'id_estado');
    }
}
