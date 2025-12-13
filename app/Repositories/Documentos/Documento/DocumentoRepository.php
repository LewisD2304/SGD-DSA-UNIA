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
            // Extraer el número del expediente (EXP-001-2025 -> 001)
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
        $query = $this->model::query()
            ->with($relaciones)
            ->where(function ($q) use ($idArea) {
                // Propios creados por el área
                $q->where('id_area_remitente', $idArea)
                    // O derivados al área y ya recepcionados
                    ->orWhere(function ($q2) use ($idArea) {
                        $q2->where('id_area_destino', $idArea)
                            ->whereNotNull('fecha_recepcion_documento');
                    });
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
