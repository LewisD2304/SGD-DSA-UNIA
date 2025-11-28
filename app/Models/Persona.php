<?php

namespace App\Models;

use App\Models\Seguridad\Usuario;
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
        'estado_persona'

    ];

    protected $hidden = [
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona');
    }
}
