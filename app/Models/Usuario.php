<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Foundation\Auth\User as Autenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditoriaTrait;

class Usuario extends Autenticatable
{
    use AuditoriaTrait, SoftDeletes;

    const CREATED_AT = 'au_fechacr';
    const UPDATED_AT = 'au_fechamd';
    const DELETED_AT = 'au_fechael';

    protected $table = 'ta_usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre_usuario',
        'estado_usuario',
        'clave_usuario',
        'id_rol',
        'id_persona'
    ];

    /**
     * VARIABLES QUE NO SE VAN A MOSTRAR O PROTEGER
     */
    protected $hidden = [
        'clave_usuario',
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    protected $casts = [
        'estado_usuario' => EstadoEnum::class,
    ];

    //FUNCION PARA AUTENTICACION
    //public function getAuthClave() { return $this->clave_usuario; }

    /**
     * RELACIONES
     */

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    // Especifica el campo que Laravel usará para autenticación
    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }

    public function getAuthPassword()
    {
        return $this->clave_usuario;
    }

    //sirve para hacer querys mas simplificadas
        // Función buscar usuario por nombre completo de la persona, nombre de usuario y rol
    public function scopeBuscar($query, $buscar)
    {
        if (empty($buscar)) {
            return $query;
        }

        return $query->where(function ($q) use ($buscar) {
            $q->where('nombre_usuario', 'like', "%{$buscar}%")
                ->orWhereHas('persona', function ($subQuery) use ($buscar) {
                    $subQuery->where(function ($q2) use ($buscar) {
                        $q2->orWhere('numerodocumento_persona', 'LIKE', "%{$buscar}%")
                            ->orWhere('nombres_persona', 'LIKE', "%{$buscar}%")
                            ->orWhere('apellido_paterno_persona', 'LIKE', "%{$buscar}%")
                            ->orWhere('apellido_materno_persona', 'LIKE', "%{$buscar}%")
                            ->orWhereRaw("CONCAT(COALESCE(nombres_persona, ''), ' ', COALESCE(apellido_paterno_persona, ''), ' ', COALESCE(apellido_materno_persona, '')) LIKE ?", ["%{$buscar}%"]);
                    });
                })
                ->orWhereHas('roles', function ($q) use ($buscar) {
                    $q->where('nombre_rol', 'like', "%{$buscar}%");
                });
        });
    }

    // Scope para filtrar por estado
    public function scopeEstado($query, $estado)
    {
        if ($estado == null) {
            return $query;
        }

        return $query->where('estado_usuario', $estado);
    }

    // Scope para limitar la búsqueda
    public function scopeLimite($query, $limite)
    {
        if ($limite == null) {
            return $query;
        }

        return $query->limit($limite);
    }

    // Función verificar credenciales
    public function verificarCredenciales($clave): bool
    {
        return password_verify($clave, $this->clave_usuario);
    }


}
