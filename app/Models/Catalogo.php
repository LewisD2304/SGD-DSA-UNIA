<?php

namespace App\Models;

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
        'id_padre',
        'descripcion_catalogo',
        'abreviatura_catalogo',
        'estado_catalogo',
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    protected $hidden = [
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    public function padre()
    {
        return $this->belongsTo(Catalogo::class, 'id_padre');
    }

    public function hijos()
    {
        return $this->hasMany(Catalogo::class, 'id_padre');
    }


}
