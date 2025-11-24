<?php

namespace App\Traits;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait AuditoriaTrait
{

    use SoftDeletes;
    /**
     * Configuración automática de nombres de columnas de fecha
     * Laravel buscará estas constantes en el Trait.
     */

    public function getCreatedAtColumn() { return 'au_fechacr'; }
    public function getUpdatedAtColumn() { return 'au_fechamd'; }
    public function getDeletedAtColumn() { return 'au_fechael'; }

     /**
     * Boot del Trait: Se ejecuta automáticamente al usar el Trait.
     * Reemplaza la necesidad de escribir boot() en cada modelo.
     */
    protected static function bootAuditoriaTrait()
    {
       static::creating(function ($model) {
            if(Auth::check()) {
                $model->au_usuariocr = Auth::id();
            }
        });

        static::updating(function ($model) {
            if(Auth::check()) {
                $model->au_usuariomd = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if(Auth::check()) {
                $model->au_usuarioel = Auth::id();
                $model->save();
            }
        });
    }
}
