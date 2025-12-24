<?php

namespace App\Repositories\Documentos\Documento;

use App\Models\Documento;
use App\Traits\BaseRepositoryTrait;

class DocumentoRepository implements DocumentoRepositoryInterface
{
    use BaseRepositoryTrait;
    protected $model;

    public function __construct(Documento $documento)
    {
        $this->model = $documento;
    }

    // Verificar si un expediente existe
    public function existeExpediente(string $expediente): bool
    {
        return $this->model::where('expediente_documento', $expediente)->exists();
    }

    // Generar número de expediente correlativo
    public function generarExpediente(): string
    {
        $anioActual = now()->year;

        // Obtener el último expediente del año actual
        $ultimoExpediente = $this->model::where('expediente_documento', 'LIKE', "EXP-%-{$anioActual}")
            ->orderBy('id_documento', 'desc')
            ->first();

        if ($ultimoExpediente) {
            // Extraer el número del expediente
            preg_match('/EXP-(\d+)-/', $ultimoExpediente->expediente_documento, $matches);
            $ultimoNumero = isset($matches[1]) ? intval($matches[1]) : 0;
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }

        return sprintf('EXP-%03d-%d', $nuevoNumero, $anioActual);
    }

    public function listarPendientesPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        $query = $this->model::query()
            ->with($relaciones)
            ->whereNull('fecha_recepcion_documento');

        if (!empty($buscar)) {
            $query->buscar($buscar);
        }

        return $query->orderBy($columnaOrden, $orden)->paginate($paginado);
    }

    public function listarPaginadoPorArea(int $idArea, int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        // Obtener IDs de documentos que el área ha procesado (creado o recepcionado)
        $idsDocumentosRecepcionados = \DB::table('ta_movimiento')
            ->where('id_area_destino', $idArea)
            ->where('id_estado', 8) // Estado RECEPCIONADO
            ->pluck('id_documento')
            ->toArray();

        $query = $this->model::query()
            ->with($relaciones)
            ->where(function ($q) use ($idArea, $idsDocumentosRecepcionados) {
                // Documentos creados por el área
                $q->where('id_area_remitente', $idArea)
                    // O documentos que el área recepcionó alguna vez
                    ->orWhereIn('id_documento', $idsDocumentosRecepcionados);
            });

        if (!empty($buscar)) {
            $query->buscar($buscar);
        }

        return $query->orderBy($columnaOrden, $orden)->paginate($paginado);
    }

    public function listarPendientesPorArea(int $idArea, int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        $query = $this->model::query()
            ->with($relaciones)
            ->whereNull('fecha_recepcion_documento')
            ->where('id_area_destino', $idArea);

        if (!empty($buscar)) {
            $query->buscar($buscar);
        }

        return $query->orderBy($columnaOrden, $orden)->paginate($paginado);
    }
}
