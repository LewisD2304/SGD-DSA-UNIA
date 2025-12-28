<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditoriaTrait;

class Movimiento extends Model
{
    use AuditoriaTrait, softDeletes;

    const CREATED_AT = 'au_fechacr';
    const UPDATED_AT = 'au_fechamd';
    const DELETED_AT = 'au_fechael';

    protected $table = 'ta_movimiento';
    protected $primaryKey = 'id_movimiento';

    public $timestamps = true;

    protected $fillable = [
        'tipo_cargo_catalogo',
        'id_documento',
        'id_estado',
        'observacion_doc_movimiento',
        'id_area_origen',
        'id_area_destino'
    ];

    protected $hidden = [
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    public function documento()
    {
        return $this->belongsTo(Documento::class, 'id_documento');
    }
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado');
    }

    // Relaciones con áreas (origen y destino)
    public function areaOrigen()
    {
        return $this->belongsTo(Area::class, 'id_area_origen', 'id_area');
    }

    public function areaDestino()
    {
        return $this->belongsTo(Area::class, 'id_area_destino', 'id_area');
    }
}
