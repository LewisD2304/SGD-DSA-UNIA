<?php

namespace App\Models;

use App\Enums\EstadoEnum;
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
        'expediente_documento',
        'folio_documento',
        'id_area_remitente',
        'id_area_destino',
        'fecha_despacho_documento',
        'fecha_emision_documento',
        'fecha_recepcion_documento',
        'asunto_documento',
        'observacion_documento',
        'id_estado',
        'tipo_documento_catalogo',
        'nombre_archivo_original',
        'id_area'
    ];

    protected $hidden = [
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];


    public function scopeBuscar($query, $buscar)
    {
        if ($buscar == null ) {
            return $query;
        }

        return $query->where(function ($query) use ($buscar) {
            $query->where('numero_documento', 'LIKE', "%$buscar%")
                ->orWhere('expediente_documento', 'LIKE', "%$buscar%")
                ->orWhere('folio_documento', 'LIKE', "%$buscar%")
                ->orWhere('asunto_documento', 'LIKE', "%$buscar%");
        });
    }

    public function scopeEstado($query, $estado)
    {
        if ($estado == null) {
            return $query;
        }

        return $query->where('id_estado', $estado);
    }

    //RELACIONES
    public function area() {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function areaRemitente() {
        return $this->belongsTo(Area::class, 'id_area_remitente');
    }

    public function areaDestino() {
        return $this->belongsTo(Area::class, 'id_area_destino');
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

    public function archivos() {
        return $this->hasMany(ArchivoDocumento::class, 'id_documento', 'id_documento')->orderBy('orden');
    }
}
