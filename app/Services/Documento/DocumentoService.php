<?php

namespace App\Services\Documento;

use App\Models\Documento;
use App\Repositories\Documentos\Documento\DocumentoRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentoService
{
     public function __construct(private DocumentoRepositoryInterface $repository){}

    // Listar todos las personas
    public function listar()
    {
        return $this->repository->listar();
    }

    public function listarPorArea(int $idArea, int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPaginadoPorArea($idArea, $paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    // Encontrar un usuario por id
    public function obtenerPorId(int $id, array $relaciones = [])
    {
        return $this->repository->obtenerPorId($id, $relaciones);
    }

    // Listar usuarios paginados con relaciones precargadas y búsqueda
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_persona', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPaginado($paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    // Listar solo documentos pendientes (sin fecha de recepción)
    public function listarPendientesPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_persona', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPendientesPaginado($paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    public function listarPendientesPorArea(int $idArea, int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPendientesPorArea($idArea, $paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    // Buscar usuarios por coincidencia
    public function buscar(?string $buscar)
    {
        return $this->repository->buscar($buscar);
    }

    // Registrar un nuevo documento
    public function registrar(array $datos)
    {
        DB::beginTransaction();

        try {
            // Generar expediente automáticamente
            $expedienteGenerado = $this->repository->generarExpediente();
            $datos['expediente_documento'] = $expedienteGenerado;

            // Obtener ID del estado "DERIVADO" de la tabla ta_estado
            $estadoDerivado = DB::table('ta_estado')
                ->where('nombre_estado', 'DERIVADO')
                ->first();

            if ($estadoDerivado) {
                $datos['id_estado'] = $estadoDerivado->id_estado;
            } else {
                throw new \Exception('Estado "DERIVADO" no encontrado en la base de datos.');
            }

            // Registrar el documento con estado DERIVADO
            $documento = $this->repository->registrar($datos);

            DB::commit();

            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al registrar el documento.'.$e->getMessage());
        }
    }

    // Modificar una persona
    public function modificar(array $datos, Documento $documento)
    {
        DB::beginTransaction();

        try {

            $documento = $this->repository->modificar($datos, $documento);

            DB::commit();
            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al modificar el documento.');
        }
    }


    // Eliminar una persona
    public function eliminar(Documento $documento, array $relaciones = [])
    {
        DB::beginTransaction();

        try {
            if ($this->repository->verificarRelaciones($documento, $relaciones)) {
                throw new \Exception('No se puede eliminar al documento porque tiene relaciones existentes.');
            }

            $this->repository->eliminar($documento);

            DB::commit();
            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            throw new \Exception('Ocurrió un error al eliminar documento.'.$e->getMessage());
        }
    }

    // Recepcionar un documento pendiente
    public function recepcionar(Documento $documento, ?Carbon $fecha = null)
    {
        DB::beginTransaction();

        try {
            $fechaRecepcion = $fecha?->toDateString() ?? Carbon::now()->toDateString();

            // Estado al recepcionar: intentar "RECEPCIONADO", si no existe usar "EN TRÁMITE"
            $estadoRecepcionado = DB::table('ta_estado')
                ->where('nombre_estado', 'RECEPCIONADO')
                ->first();

            if (!$estadoRecepcionado) {
                $estadoRecepcionado = DB::table('ta_estado')
                    ->where('nombre_estado', 'EN TRÁMITE')
                    ->first();
            }

            $documento = $this->repository->modificar([
                'fecha_recepcion_documento' => $fechaRecepcion,
                'id_estado' => $estadoRecepcionado?->id_estado,
            ], $documento);

            DB::commit();

            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al recepcionar el documento.');
        }
    }
}
