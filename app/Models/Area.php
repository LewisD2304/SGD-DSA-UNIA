<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditoriaTrait;

class Area extends Model
{
    use AuditoriaTrait, SoftDeletes;

    const CREATED_AT = 'au_fechacr';
    const UPDATED_AT = 'au_fechamd';
    const DELETED_AT = 'au_fechael';

    protected $table = 'ta_area';
    protected $primaryKey = 'id_area';

    protected $fillable = [
        'nombre_area',
        'siglas_area',
        'estado_area',
        'id_persona'
    ];

    protected $hidden = [
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    protected $casts = [
        'estado_area' => EstadoEnum::class,
    ];

    // Relaciones
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'id_area', 'id_area');
    }

    public function personas()
    {
        return $this->hasMany(Persona::class, 'id_area', 'id_area');
    }

    // Scopes
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado_area', $estado);
    }

    public function scopeBuscar($query, ?string $buscar)
    {
        if (empty($buscar)) {
            return $query;
        }

        return $query->where(function ($query) use ($buscar) {
            $query->where('nombre_area', 'like', "%{$buscar}%")
                  ->orWhere('siglas_area', 'like', "%{$buscar}%");
        });
    }
}
