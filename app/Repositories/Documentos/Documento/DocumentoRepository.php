<?php

namespace App\Repositories\Documentos\Documento;

use App\Models\Documento;
use App\Traits\BaseRepositoryTrait;
use Illuminate\Support\Facades\DB;

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
        $query = $this->model::query()
            ->with($relaciones)
            ->where(function ($q) use ($idArea) {
                // Documentos creados por el área (siempre visibles en "Mis Documentos")
                $q->where('id_area_remitente', $idArea)
                    // O documentos que tiene actualmente en su poder (recepcionados y aún en destino)
                    ->orWhere(function ($subQuery) use ($idArea) {
                        $subQuery->where('id_area_destino', $idArea)
                            ->whereNotNull('fecha_recepcion_documento');
                    });
            });

        if (!empty($buscar)) {
            $query->buscar($buscar);
        }

        return $query->orderBy($columnaOrden, $orden)->paginate($paginado);
    }

    public function listarPaginadoPorAreaConFiltros(int $idArea, int $paginado = 10, ?string $buscar = null, ?string $fechaInicio = null, ?string $fechaFin = null, ?string $idEstado = null, ?string $idOficinaCatalogo = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        $query = $this->model::query()
            ->with($relaciones)
            ->where(function ($q) use ($idArea) {
                // Documentos creados por el área (siempre visibles en "Mis Documentos")
                $q->where('id_area_remitente', $idArea)
                    // O documentos que tiene actualmente en su poder (recepcionados y aún en destino)
                    ->orWhere(function ($subQuery) use ($idArea) {
                        $subQuery->where('id_area_destino', $idArea)
                            ->whereNotNull('fecha_recepcion_documento');
                    });
            })
            // Excluir documentos anulados
            ->whereHas('estado', function($q) {
                $q->where('nombre_estado', '!=', 'ANULADO');
            });

        if (!empty($buscar)) {
            $query->buscar($buscar);
        }

        // Filtro por rango de fechas
        if (!empty($fechaInicio)) {
            $query->whereDate('au_fechacr', '>=', $fechaInicio);
        }
        if (!empty($fechaFin)) {
            $query->whereDate('au_fechacr', '<=', $fechaFin);
        }

        // Filtro por estado
        if (!empty($idEstado)) {
            $query->where('id_estado', $idEstado);
        }

        // Filtro por oficina
        if (!empty($idOficinaCatalogo)) {
            $query->where('oficina_catalogo', $idOficinaCatalogo);
        }

        return $query->orderBy($columnaOrden, $orden)->paginate($paginado);
    }

    public function listarPendientesPorArea(int $idArea, int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        $query = $this->model::query()
            ->with($relaciones)
            ->where('id_area_destino', $idArea)
            ->where(function($q) {
                // Documentos sin recepcionar O solicitudes de rectificación EN ESPERA (estado 10)
                $q->whereNull('fecha_recepcion_documento')
                  ->orWhere('id_estado', 10); // 10=Solicitud rectificación (aún sin resolver)
            })
            ->whereHas('estado', function ($q) {
                // Solo estados considerados pendientes para el área
                $q->whereIn('nombre_estado', [
                    'DERIVADO',
                    'OBSERVADO',
                    'SUBSANADO',
                    'RETORNADO',
                    'PARA ARCHIVAR',
                    'RECEPCION SUBSANADA',
                ])->orWhere('id_estado', 10); // 10=Solicitud rectificación
            });

        if (!empty($buscar)) {
            $query->buscar($buscar);
        }

        return $query->orderBy($columnaOrden, $orden)->paginate($paginado);
    }
}
