<?php

namespace App\Models;

use App\Traits\AuditoriaTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchivoDocumento extends Model
{
    use AuditoriaTrait, SoftDeletes;

    const CREATED_AT = 'au_fechacr';
    const UPDATED_AT = 'au_fechamd';
    const DELETED_AT = 'au_fechael';

    protected $table = 'ta_archivo_documento';
    protected $primaryKey = 'id_archivo_documento';

    public $timestamps = true;

    protected $fillable = [
        'id_documento',
        'nombre_original',
        'nombre_archivo',
        'ruta_archivo',
        'extension',
        'tamanio',
        'orden'
    ];

    protected $hidden = [
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    // Relación con Documento
    public function documento()
    {
        return $this->belongsTo(Documento::class, 'id_documento', 'id_documento');
    }

    // Accessor para tamaño formateado
    public function getTamanioFormateadoAttribute()
    {
        if ($this->tamanio >= 1048576) {
            return number_format($this->tamanio / 1048576, 2) . ' MB';
        }
        return number_format($this->tamanio / 1024, 0) . ' KB';
    }

    // Accessor para icono según extensión
    public function getIconoAttribute()
    {
        $ext = strtolower($this->extension);

        $iconos = [
            'pdf' => 'ki-file-sheet',
            'doc' => 'ki-file-sheet',
            'docx' => 'ki-file-sheet',
            'xls' => 'ki-file-sheet',
            'xlsx' => 'ki-file-sheet',
            'ppt' => 'ki-file-sheet',
            'pptx' => 'ki-file-sheet',
            'png' => 'ki-picture',
            'jpg' => 'ki-picture',
            'jpeg' => 'ki-picture',
            'gif' => 'ki-picture',
            'zip' => 'ki-folder',
            'rar' => 'ki-folder',
        ];

        return $iconos[$ext] ?? 'ki-file';
    }

    // Accessor para color según extensión
    public function getColorAttribute()
    {
        $ext = strtolower($this->extension);

        $colores = [
            'pdf' => 'danger',
            'doc' => 'primary',
            'docx' => 'primary',
            'xls' => 'success',
            'xlsx' => 'success',
            'ppt' => 'warning',
            'pptx' => 'warning',
            'png' => 'info',
            'jpg' => 'info',
            'jpeg' => 'info',
        ];

        return $colores[$ext] ?? 'secondary';
    }
}
