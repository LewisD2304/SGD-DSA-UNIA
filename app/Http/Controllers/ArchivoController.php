<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    /**
     * Descargar/visualizar archivo desde el disco 'share'
     */
    public function descargar(Request $request)
    {
        $ruta = $request->query('ruta');
        $nombre = $request->query('nombre', 'documento');

        if (!$ruta) {
            abort(400, 'Ruta del archivo no especificada');
        }

        $disk = Storage::disk('share');

        if (!$disk->exists($ruta)) {
            abort(404, 'Archivo no encontrado');
        }

        // Obtener path completo del archivo
        $pathCompleto = $disk->path($ruta);

        // Retornar archivo para visualización/descarga
        return response()->file($pathCompleto, [
            'Content-Disposition' => 'inline; filename="' . $nombre . '"'
        ]);
    }
}
