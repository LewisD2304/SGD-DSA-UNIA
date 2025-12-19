<?php

namespace App\Services\Documento;

use App\Models\Documento;
use App\Repositories\Documentos\Documento\DocumentoRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentoService
{
     public function __construct(private DocumentoRepositoryInterface $repository){}

    // Listar todos los documentos
    public function listar()
    {
        return $this->repository->listar();
    }

    public function listarPorArea(int $idArea, int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPaginadoPorArea($idArea, $paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    // Encontrar un documento por id
    public function obtenerPorId(int $id, array $relaciones = [])
    {
        return $this->repository->obtenerPorId($id, $relaciones);
    }

    // Listar documentos paginados con relaciones precargadas y búsqueda
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPaginado($paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    // Listar solo documentos pendientes
    public function listarPendientesPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPendientesPaginado($paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    public function listarPendientesPorArea(int $idArea, int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPendientesPorArea($idArea, $paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    // Buscar documentos por coincidencia
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

    // Modificar un documento
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

    // Eliminar un documento
    public function eliminar(Documento $documento, array $relaciones = [])
    {
        DB::beginTransaction();

        try {
            if ($this->repository->verificarRelaciones($documento, $relaciones)) {
                throw new \Exception('No se puede eliminar el documento porque tiene relaciones existentes.');
            }

            $this->repository->eliminar($documento);

            DB::commit();
            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al eliminar el documento: '.$e->getMessage());
        }
    }

    // Recepcionar un documento pendiente
    public function recepcionar(Documento $documento, ?Carbon $fecha = null)
    {
        DB::beginTransaction();

        try {
            // Usar Carbon::now() para capturar la fecha y hora actual con la zona horaria del servidor
            $fechaRecepcion = $fecha ?? Carbon::now();

            // Convertir a formato DATETIME de MySQL: YYYY-MM-DD HH:MM:SS
            $fechaRecepcionFormato = $fechaRecepcion->format('Y-m-d H:i:s');

            // Estado al recepcionar: intentar "RECEPCIONADO", si no existe usar "EN TRÁMITE"
            $estadoRecepcionado = DB::table('ta_estado')
                ->where('nombre_estado', 'RECEPCIONADO')
                ->first();

            if (!$estadoRecepcionado) {
                $estadoRecepcionado = DB::table('ta_estado')
                    ->where('nombre_estado', 'EN TRÁMITE')
                    ->first();
            }

            if (!$estadoRecepcionado) {
                throw new \Exception('No se encontró un estado válido para recepcionar (RECEPCIONADO o EN TRÁMITE).');
            }

            $documento = $this->repository->modificar([
                'fecha_recepcion_documento' => $fechaRecepcionFormato,
                'id_estado' => $estadoRecepcionado->id_estado,
            ], $documento);

            DB::commit();

            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al recepcionar el documento.');
        }
    }

    // Derivar un documento a otra área
    public function derivar(int $idDocumento, int $idAreaDerivar, ?string $observaciones = null)
    {
        DB::beginTransaction();

        try {
            $documento = $this->repository->obtenerPorId($idDocumento);

            if (!$documento) {
                throw new \Exception('Documento no encontrado.');
            }

            // Log para debug
            Log::info('Derivando documento', [
                'id_documento' => $idDocumento,
                'area_destino_anterior' => $documento->id_area_destino,
                'area_destino_nueva' => $idAreaDerivar,
                'estado_actual' => $documento->id_estado
            ]);

            // Obtener el estado "DERIVADO"
            $estadoDerivado = DB::table('ta_estado')
                ->where('nombre_estado', 'DERIVADO')
                ->first();

            if (!$estadoDerivado) {
                throw new \Exception('Estado "DERIVADO" no encontrado en la base de datos.');
            }

            // Actualizar el documento
            $documento = $this->repository->modificar([
                'id_area_destino' => $idAreaDerivar,
                'id_estado' => $estadoDerivado->id_estado,
                'fecha_recepcion_documento' => null,
            ], $documento);

            // Log después de actualizar
            Log::info('Documento actualizado', [
                'id_documento' => $documento->id_documento,
                'id_area_destino' => $documento->id_area_destino,
                'id_estado' => $documento->id_estado,
                'fecha_recepcion' => $documento->fecha_recepcion_documento
            ]);

            // Registrar el movimiento en ta_movimiento con campos de auditoría
            DB::table('ta_movimiento')->insert([
                'id_documento' => $idDocumento,
                'id_estado' => $estadoDerivado->id_estado,
                'observacion_doc_movimiento' => $observaciones,
                'au_fechacr' => Carbon::now(),
                'au_fechamd' => Carbon::now(),
            ]);

            DB::commit();

            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al derivar documento', ['error' => $e->getMessage()]);
            throw new \Exception('Ocurrió un error al derivar el documento: '.$e->getMessage());
        }
    }

    // Procesar transición de estado según la tabla ta_transicion
    public function procesarTransicion(int $idDocumento, int $idTransicion, array $datos = [])
    {
        DB::beginTransaction();

        try {
            $documento = $this->repository->obtenerPorId($idDocumento);

            if (!$documento) {
                throw new \Exception('Documento no encontrado.');
            }

            // Obtener la transición
            $transicion = DB::table('ta_transicion')
                ->where('id_transicion', $idTransicion)
                ->first();

            if (!$transicion) {
                throw new \Exception('Transición no encontrada.');
            }

            // Validar que el estado actual del documento coincida con la transición
            if ($documento->id_estado != $transicion->id_estado_actual_transicion) {
                throw new \Exception('El estado actual del documento no permite esta transición.');
            }

            // Preparar datos de actualización del documento
            $datosDocumento = [
                'id_estado' => $transicion->id_estado_siguiente_transicion,
            ];

            // Si la transición es RECEPCIONAR, actualizar fecha de recepción
            if (strtoupper($transicion->evento_transicion) === 'RECEPCIONAR') {
                $datosDocumento['fecha_recepcion_documento'] = Carbon::now()->format('Y-m-d H:i:s');
            }

            // Si la transición es DERIVAR, actualizar área destino y limpiar fecha recepción
            if (strtoupper($transicion->evento_transicion) === 'DERIVAR' && isset($datos['id_area_destino'])) {
                $datosDocumento['id_area_destino'] = $datos['id_area_destino'];
                $datosDocumento['fecha_recepcion_documento'] = null;
            }

            // Si la transición es DEVOLVER y hay área destino
            if (strtoupper($transicion->evento_transicion) === 'DEVOLVER' && isset($datos['id_area_destino'])) {
                $datosDocumento['id_area_destino'] = $datos['id_area_destino'];
                $datosDocumento['fecha_recepcion_documento'] = null;
            }

            // Actualizar el documento
            $documento = $this->repository->modificar($datosDocumento, $documento);

            // Registrar el movimiento con las observaciones
            DB::table('ta_movimiento')->insert([
                'id_documento' => $idDocumento,
                'id_estado' => $transicion->id_estado_siguiente_transicion,
                'observacion_doc_movimiento' => $datos['observacion'] ?? null,
                'au_fechacr' => Carbon::now(),
                'au_fechamd' => Carbon::now(),
            ]);

            DB::commit();

            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al procesar la transición: '.$e->getMessage());
        }
    }
}
