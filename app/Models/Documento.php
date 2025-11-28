<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditoriaTrait;

class Documento extends Model
{
    use AuditoriaTrait, softDeletes;

    const CREATED_AT = 'au_fechacr';
    const UPDATED_AT = 'au_fechamd';
    const DELETED_AT = 'au_fechael';

    protected $table = 'ta_documento';
    protected $primaryKey = 'id_documento';

    public $timestamps = true;

    protected $fillable = [
        'numero_documento',
        'fecha_despacho_documento',
        'fecha_emision_documento',
        'fecha_recepcion_documento',
        'asunto_documento',
        'observacion_documento',
        'id_estado',
        'tipo_documento_catalogo',
        'ruta_documento',
        'id_persona',
        'id_area'
    ];

    protected $hidden = [
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    //RELACIONES
    public function area() {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function persona() {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function estado() {
        return $this->belongsTo(Estado::class, 'id_estado');
    }

    public function tipoDocumento() {
        return $this->belongsTo(Catalogo::class, 'tipo_documento_catalogo', 'id_catalogo');
    }

    public function movimientos() {
        return $this->hasMany(Movimiento::class, 'id_documento');
    }


}
