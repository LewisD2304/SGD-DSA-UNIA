<?php

namespace App\Traits;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait AuditoriaTrait
{
     /**
     * Boot del Trait: Se ejecuta automáticamente al usar el Trait.
     * Reemplaza la necesidad de escribir boot() en cada modelo.
     */
    protected static function bootAuditoriaTrait()
    {
       static::creating(function ($model) {
            $model->au_usuariocr = Auth::id();
        });

        static::updating(function ($model) {
            $model->au_usuariomd = Auth::id();
        });

        static::deleting(function ($model) {
            $model->au_usuarioel = Auth::id();
            $model->save();

        });
    }
}
