<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditoriaTrait;

class Movimiento extends Model
{
    use AuditoriaTrait;

    protected $table = 'ta_movimiento';
    protected $primaryKey = 'id_movimiento';

    public $timestamps = true;

    protected $fillable = [
        'tipo_cargo_catalogo',
        'id_documento',
        'id_estado',
        'au_fechacr',
        'au_fechamd',
        'au_fechael',
        'au_usuariocr',
        'au_usuariomd',
        'au_usuarioel'
    ];

    public function documento() { return $this->belongsTo(Documento::class, 'id_documento'); }
    public function estado() { return $this->belongsTo(Estado::class, 'id_estado'); }






}
