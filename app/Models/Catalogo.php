<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditoriaTrait;

class Catalogo extends Model
{
    use AuditoriaTrait, softDeletes;

    const CREATED_AT = 'au_fechacr';
    const UPDATED_AT = 'au_fechamd';
    const DELETED_AT = 'au_fechael';

    protected $table = 'ta_catalogo';
    protected $primaryKey = 'id_catalogo';

    public $timestamps = true;

    protected $fillable = [
        'id_padre', // Relación foranea, id recursivo
        'descripcion_catalogo',
        'abreviatura_catalogo',
        'estado_catalogo',
    ];

    protected $casts = [
        'estado_catalogo' => EstadoEnum::class,
    ];

    protected $hidden = [
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];


    // Relación de padre
    public function padre()
    {
        return $this->belongsTo(Catalogo::class, 'id_padre');
    }

    // Relación de hijos
    public function hijos()
    {
        return $this->hasMany(Catalogo::class, 'id_padre')->where('estado_catalogo', EstadoEnum::HABILITADO);
    }

    // Relación de acciones
    public function acciones()
    {
        return $this->hasMany(Accion::class, 'accion_catalogo');
    }

    // Relación de tipo documento
    public function tipoDocumento()
    {
        return $this->hasMany(Documento::class, 'tipo_documento_catalogo');
    }

    // Relación de tipo documento de persona
    public function tipoDocumentoPersona()
    {
        return $this->hasMany(Persona::class, 'tipodocumento_catalogo');
    }

    // Scope para buscar coincidencias en nombre y descripción
    public function scopeBuscar($query, $buscar)
    {
        if ($buscar == null) {
            return $query;
        }

        return $query->where(function ($query) use ($buscar) {
            $query->where('descripcion_catalogo', 'LIKE', "%$buscar%")
                ->orWhere('abreviatura_catalogo', 'LIKE', "%$buscar%");
        });
    }

    // Scope para filtrar por estado
    public function scopeEstado($query, $estado)
    {
        if ($estado == null) {
            return $query;
        }

        return $query->where('estado_catalogo', $estado);
    }


}
