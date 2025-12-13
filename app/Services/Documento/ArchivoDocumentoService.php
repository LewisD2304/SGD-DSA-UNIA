<?php

namespace App\Services\Documento;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArchivoDocumentoService
{
    /**
     * Obtener el nombre del archivo formateado con tamaño
     */
    public function obtenerNombreConTamanio(string $ruta, string $nombreSinExtension, string $extension): ?string
    {
        $disk = Storage::disk('share');

        if (!$disk->exists($ruta)) {
            return null;
        }

        $tamanioBytes = $disk->size($ruta);
        $tamanio = $tamanioBytes >= 1048576
            ? number_format($tamanioBytes / 1048576, 2) . ' MB'
            : number_format($tamanioBytes / 1024, 0) . ' KB';

        $nombreCompleto = "{$nombreSinExtension}.{$extension}";

        return "{$nombreCompleto} ({$tamanio})";
    }

    /**
     * Generar información del archivo
     */
    protected function generarInfoArchivo(mixed $archivo): array
    {
        $nombreOriginal = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $archivo->getClientOriginalExtension();

        // Sanitizar nombre: eliminar caracteres especiales, reemplazar espacios con guiones
        $nombreSanitizado = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '-', $nombreOriginal));

        // Timestamp corto para evitar duplicados: YYYYMMDDHHMM
        $timestamp = now()->format('YmdHis');

        // Nombre final: nombreOriginal_timestamp.extension
        $nombreFinal = "{$nombreSanitizado}_{$timestamp}.{$extension}";

        return [
            'nombreOriginal' => $nombreOriginal,
            'extension' => $extension,
            'nombreFinal' => $nombreFinal,
        ];
    }

    /**
     * Obtener ruta completa del archivo
     */
    protected function obtenerRutaCompleta(string $ruta, string $nombreFinal): array
    {
        // Convertir puntos a slashes y capitalizar cada segmento
        $ruta = collect(explode('.', $ruta))
            ->map(fn($segmento) => ucfirst($segmento))
            ->implode('/');

        $carpetaCompleta = rtrim($ruta, '/') . '/';
        $rutaRelativa = $carpetaCompleta . $nombreFinal;

        return compact('carpetaCompleta', 'rutaRelativa');
    }

    /**
     * Asegurar que el directorio existe
     */
    protected function asegurarDirectorio(string $carpetaCompleta, $storage): void
    {
        if (!is_dir($storage->path($carpetaCompleta)) && !mkdir($storage->path($carpetaCompleta), 0755, true) && !is_dir($storage->path($carpetaCompleta))) {
            throw new \Exception("No se pudo crear la carpeta '{$carpetaCompleta}'.");
        }
    }

    /**
     * Eliminar archivo temporal
     */
    public function eliminarArchivoTemporal(string $ruta, ?string $disco = 'local'): void
    {
        try {
            if (Str::startsWith($ruta, 'temporal/') && Storage::disk($disco)->exists($ruta)) {
                Storage::disk($disco)->delete($ruta);
            }
        } catch (\Throwable $e) {
            // Silenciar error si no se puede eliminar
        }
    }

    /**
     * Guardar archivo en el disco
     */
    public function guardarArchivo(mixed $archivo, string $ruta, ?string $disco = 'share'): ?array
    {
        $storage = Storage::disk($disco);

        // Generar nombre y datos del archivo
        $info = $this->generarInfoArchivo($archivo);
        $rutaCompleta = $this->obtenerRutaCompleta($ruta, $info['nombreFinal']);

        // Asegurar que el directorio exista
        $this->asegurarDirectorio($rutaCompleta['carpetaCompleta'], $storage);

        if (!$storage->putFileAs($rutaCompleta['carpetaCompleta'], $archivo, $info['nombreFinal'])) {
            throw new \Exception("No se pudo guardar el archivo '{$info['nombreFinal']}' en el disco '{$disco}'.");
        }

        // Retorna la información para guardar en la base de datos
        return [
            'nombre_archivo' => $info['nombreOriginal'],
            'nombre_archivo_original' => $info['nombreOriginal'] . '.' . $info['extension'],
            'ruta_archivo' => $rutaCompleta['rutaRelativa'],
            'extension_archivo' => $info['extension'],
        ];
    }

    /**
     * Modificar archivo del disco
     */
    public function modificarArchivo(mixed $archivo, string $ruta, string $rutaAnterior, ?string $disco = 'share'): ?array
    {
        $storage = Storage::disk($disco);

        // Eliminar el archivo anterior si existe
        if ($rutaAnterior && $storage->exists($rutaAnterior)) {
            $storage->delete($rutaAnterior);
        }

        // Generar nuevo nombre y ruta
        $info = $this->generarInfoArchivo($archivo);
        $rutaCompleta = $this->obtenerRutaCompleta($ruta, $info['nombreFinal']);

        // Asegurar carpeta
        $this->asegurarDirectorio($rutaCompleta['carpetaCompleta'], $storage);

        // Guardar nuevo archivo
        if (!$storage->putFileAs($rutaCompleta['carpetaCompleta'], $archivo, $info['nombreFinal'])) {
            throw new \Exception("No se pudo guardar el nuevo archivo '{$info['nombreFinal']}' en el disco '{$disco}'.");
        }

        // Retorna la información para guardar en la base de datos
        return [
            'nombre_archivo' => $info['nombreOriginal'],
            'nombre_archivo_original' => $info['nombreOriginal'] . '.' . $info['extension'],
            'ruta_archivo' => $rutaCompleta['rutaRelativa'],
            'extension_archivo' => $info['extension'],
        ];
    }

    /**
     * Eliminar archivo físico
     */
    public function eliminarArchivo(string $ruta, ?string $disco = 'share'): bool
    {
        $storage = Storage::disk($disco);

        if ($storage->exists($ruta)) {
            return $storage->delete($ruta);
        }

        return false;
    }

    /**
     * Verificar si el archivo existe
     */
    public function existeArchivo(string $ruta, ?string $disco = 'share'): bool
    {
        return Storage::disk($disco)->exists($ruta);
    }

    /**
     * Obtener el tamaño del archivo
     */
    public function obtenerTamanioArchivo(string $ruta, ?string $disco = 'share'): ?int
    {
        $storage = Storage::disk($disco);

        if (!$storage->exists($ruta)) {
            return null;
        }

        return $storage->size($ruta);
    }
}
