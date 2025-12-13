<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditoriaTrait;

class Persona extends Model
{
    use AuditoriaTrait, softDeletes;

    const CREATED_AT = 'au_fechacr';
    const UPDATED_AT = 'au_fechamd';
    const DELETED_AT = 'au_fechael';

    protected $table = 'ta_persona';

    protected $primaryKey = 'id_persona';

    public $timestamps = true;

    protected $fillable = [
        'nombres_persona',
        'apellido_paterno_persona',
        'apellido_materno_persona',
        'numerodocumento_persona',
        'tipodocuento_catalogo',
        'celular_persona',
        'correo_persona',
        'estado_persona',
        'id_area'
    ];

    protected $casts = [
        'estado_persona' => EstadoEnum::class,
    ];

    protected $hidden = [
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(Catalogo::class, 'tipodocuento_catalogo');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona');
    }

    // Scope para búsqueda por coincidencia
    public function scopeBuscar($query, $buscar)
    {
        if (empty($buscar)) {
            return $query;
    }

        return $query->where('nombres_persona', 'LIKE', "%{$buscar}%")
            ->orWhere('apellido_paterno_persona', 'LIKE', "%{$buscar}%")
            ->orWhere('apellido_materno_persona', 'LIKE', "%{$buscar}%")
            ->orWhere('numerodocumento_persona', 'LIKE', "%{$buscar}%")
            ->orWhere('correo_persona', 'LIKE', "%{$buscar}%");
    }

    // Scope para filtrar por estado
    public function scopeEstado($query, $estado)
    {
        if ($estado == null) {
            return $query;
        }

        return $query->where('estado_persona', $estado);
    }

    // Scope para limitar la búsqueda
    public function scopeLimite($query, $limite)
    {
        if ($limite == null) {
            return $query;
        }

        return $query->limit($limite);
    }
}
