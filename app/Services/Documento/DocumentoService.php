<?php

namespace App\Services\Documento;

use App\Models\ArchivoDocumento;
use App\Models\Documento;
use App\Repositories\Documentos\Documento\DocumentoRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentoService
{
    public function __construct(private DocumentoRepositoryInterface $repository) {}

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

    public function obtenerPorIdParaArea(int $id, int $idAreaUsuario, array $relaciones = [])
    {
        $documento = $this->repository->obtenerPorId($id, $relaciones);

        if ($documento && $documento->relationLoaded('archivos')) {
            // Determinar el área de origen del último DERIVAR hacia cualquier destino
            $estadoDerivado = DB::table('ta_estado')->where('nombre_estado', 'DERIVADO')->first();
            $areaOrigenUltimoDerivar = null;

            if ($estadoDerivado) {
                $ultimoDerivar = DB::table('ta_movimiento')
                    ->where('id_documento', $documento->id_documento)
                    ->where('id_estado', $estadoDerivado->id_estado)
                    ->orderBy('au_fechacr', 'desc')
                    ->first();

                $areaOrigenUltimoDerivar = $ultimoDerivar->id_area_origen ?? null;
            }

            // Conservar todos los adjuntos para cálculos posteriores
            $todosAdjuntos = $documento->archivos;

            // Base: adjuntos visibles por área del usuario/remitente/legado
            $baseAdjuntos = $todosAdjuntos
                ->filter(function ($archivo) use ($idAreaUsuario, $documento) {
                    return $archivo->id_area === null
                        || $archivo->id_area == $idAreaUsuario
                        || $archivo->id_area == $documento->id_area_remitente;
                })
                ->values();

            $documento->setRelation('archivos', $baseAdjuntos);

            // Si el usuario es el destino actual del documento, incluir también los adjuntos subidos por
            // el área que realizó el último DERIVAR (se envían con todos los cambios de esa área).
            if ($documento->id_area_destino == $idAreaUsuario && $areaOrigenUltimoDerivar) {
                $adjuntosExtra = $todosAdjuntos
                    ->filter(function ($archivo) use ($areaOrigenUltimoDerivar) {
                        return $archivo->id_area == $areaOrigenUltimoDerivar;
                    });

                $documento->setRelation(
                    'archivos',
                    $documento->archivos
                        ->merge($adjuntosExtra)
                        ->unique('id_archivo_documento')
                        ->values()
                );
            }
        }

        return $documento;
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
            throw new \Exception('Error al registrar el documento.' . $e->getMessage());
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
            throw new \Exception('Ocurrió un error al eliminar el documento: ' . $e->getMessage());
        }
    }

    public function recepcionar(Documento $documento, ?Carbon $fecha = null)
    {
        DB::beginTransaction();

        try {
            $fechaRecepcion = $fecha ?? Carbon::now();
            $usuario = Auth::user();

            // 1. VALIDACIÓN DE SEGURIDAD DEL PERFIL (CRÍTICO)
            // Verificamos que el usuario tenga persona y área asignada.
            // Si esto falla, es la razón por la que no se guardaba con "otros perfiles".
            if (!$usuario->persona || !$usuario->persona->id_area) {
                throw new \Exception("El usuario {$usuario->nombre_usuario} no tiene un Área o Persona asignada en el sistema.");
            }

            $idAreaDestino = $usuario->persona->id_area;
            $idAreaOrigen  = $documento->id_area_remitente;

            // Si el documento no tiene remitente (es huérfano), usamos el área actual para evitar error SQL
            if (!$idAreaOrigen) {
                // Opcional: Lanza error o usa un valor por defecto
                $idAreaOrigen = $idAreaDestino;
            }

            // 2. OBTENER EL ESTADO "EN TRÁMITE"
            $estadoEnTramite = DB::table('ta_estado')
                ->where(function ($query) {
                    $query->where('nombre_estado', 'LIKE', 'EN TRÁMITE')
                        ->orWhere('nombre_estado', 'LIKE', 'EN TRAMITE');
                })
                ->first();

            if (!$estadoEnTramite) {
                throw new \Exception('El estado "EN TRÁMITE" no existe en la base de datos.');
            }

            // 3. REGISTRAR HISTORIAL (TA_MOVIMIENTO)
            DB::table('ta_movimiento')->insert([
                'id_documento'               => $documento->id_documento,
                'id_estado'                  => $estadoEnTramite->id_estado,
                'id_area_origen'             => $idAreaOrigen,  // De dónde viene
                'id_area_destino'            => $idAreaDestino, // Quién lo tiene ahora (YO)
                'observacion_doc_movimiento' => 'RECEPCIÓN: Documento puesto en trámite por ' . ($usuario->nombre_usuario ?? $usuario->name),
                'au_usuariocr'               => $usuario->nombre_usuario ?? 'SISTEMA',
                'au_fechacr'                 => Carbon::now(),
            ]);

            // 4. ACTUALIZAR EL DOCUMENTO
            // Es vital actualizar el "id_area_asignada" (si tu tabla lo tiene) para saber dónde está el documento ahora.
            $datosActualizar = [
                'fecha_recepcion_documento' => $fechaRecepcion->format('Y-m-d H:i:s'),
                'id_estado'                 => $estadoEnTramite->id_estado,
            ];

            // SI tu tabla documentos tiene campo para saber en qué área está actualmente, actualízalo:
            // 'id_area_asignada' => $idAreaDestino

            $this->repository->modificar($datosActualizar, $documento);

            DB::commit();
            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            // Esto escribirá el error real en tu archivo laravel.log (storage/logs/)
            Log::error("Error recepcionar perfil {$usuario->id}: " . $e->getMessage());
            throw $e;
        }
    }

    // Derivar un documento a otra área
    public function derivar(int $idDocumento, int $idAreaDestino, ?string $observaciones = null, array $nuevosArchivos = [])
    {
        DB::beginTransaction();

        try {
            $documento = $this->repository->obtenerPorId($idDocumento);
            $usuario = Auth::user();
            $idAreaOrigen = $usuario->persona->id_area; // Mi área actual

            if (!$documento) throw new \Exception('Documento no encontrado.');

            // 1. Guardar Archivos Nuevos
            if (!empty($nuevosArchivos)) {
                $archivoService = resolve(\App\Services\Documento\ArchivoDocumentoService::class);
                $archivosInfo = $archivoService->guardarMultiplesArchivos(
                    archivos: $nuevosArchivos,
                    ruta: 'gestion/documentos/documentos',
                    idDocumento: $documento->id_documento,
                    idArea: $idAreaOrigen
                );
                foreach ($archivosInfo as $info) {
                    \App\Models\ArchivoDocumento::create($info);
                }
            }

            // 2. Obtener estado DERIVADO (ID 1)
            $estadoDerivado = DB::table('ta_estado')->where('nombre_estado', 'DERIVADO')->first();
            if (!$estadoDerivado) throw new \Exception('Estado DERIVADO no existe.');

            // 3. REGISTRAR HISTORIAL (Congelado)
            DB::table('ta_movimiento')->insert([
                'id_documento'    => $idDocumento,
                'id_estado'       => $estadoDerivado->id_estado,
                'id_area_origen'  => $idAreaOrigen,  // DE
                'id_area_destino' => $idAreaDestino, // PARA
                'observacion_doc_movimiento' => $observaciones,
                'au_usuariocr'    => $usuario->nombre_usuario,
                'au_fechacr'      => Carbon::now(),
            ]);

            // 4. ACTUALIZAR EL DOCUMENTO (Cambio de manos)
            // IMPORTANTE: No tocar id_area_remitente para conservar el área creadora original
            $this->repository->modificar([
                'id_area_destino'   => $idAreaDestino,     // Nuevo destino (ÉL)
                'id_estado'         => $estadoDerivado->id_estado,

                'fecha_recepcion_documento' => null,       // Se limpia (a la espera de recepción)

                // Fecha de salida del área
                'fecha_emision_documento'   => Carbon::now(),

                'observacion_documento'     => $observaciones
            ], $documento);

            DB::commit();

            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al derivar: ' . $e->getMessage());
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
                $usuario = Auth::user();
                $idAreaOrigen = $usuario->persona->id_area ?? null;

                // Actualizar el nuevo destino y limpiar recepción
                $datosDocumento['id_area_destino'] = $datos['id_area_destino'];
                $datosDocumento['fecha_recepcion_documento'] = null;
                $datosDocumento['fecha_emision_documento'] = Carbon::now();
            }

            // Si la transición es DEVOLVER y hay área destino
            if (strtoupper($transicion->evento_transicion) === 'DEVOLVER' && isset($datos['id_area_destino'])) {
                $datosDocumento['id_area_destino'] = $datos['id_area_destino'];
                $datosDocumento['fecha_recepcion_documento'] = null;
            }

            // Actualizar el documento
            $documento = $this->repository->modificar($datosDocumento, $documento);

            // Preparar datos del movimiento
            $usuario = Auth::user();
            $datosMovimiento = [
                'id_documento' => $idDocumento,
                'id_estado' => $datosDocumento['id_estado'] ?? $transicion->id_estado_siguiente_transicion,
                'observacion_doc_movimiento' => $datos['observacion'] ?? null,
                'au_fechacr' => Carbon::now(),
                'au_fechamd' => Carbon::now(),
            ];

            // Si es DERIVAR, registrar origen y destino
            if (strtoupper($transicion->evento_transicion) === 'DERIVAR' && isset($datos['id_area_destino'])) {
                $datosMovimiento['id_area_origen'] = $usuario->persona->id_area ?? null;
                $datosMovimiento['id_area_destino'] = $datos['id_area_destino'];
            }

            // Si es RECEPCIONAR, registrar áreas
            if (strtoupper($transicion->evento_transicion) === 'RECEPCIONAR') {
                $datosMovimiento['id_area_origen'] = $documento->id_area_remitente;
                $datosMovimiento['id_area_destino'] = $usuario->persona->id_area ?? null;
            }

            // Registrar el movimiento
            DB::table('ta_movimiento')->insert($datosMovimiento);

            DB::commit();

            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al procesar la transición: ' . $e->getMessage());
        }
    }

    /**
     * Obtener historial de documentos derivados por un área
     * Muestra todos los documentos que el área ha procesado (creado o recepcionado)
     * y que ya fueron derivados a otras áreas, registrando las fechas de recepción.
     */
    public function obtenerHistorialDerivaciones(int $idArea, ?string $buscar = null)
    {
        // Obtener IDs de documentos que el área ha recepcionado alguna vez
        $idsDocumentosRecepcionados = DB::table('ta_movimiento')
            ->where('id_area_destino', $idArea)
            ->where('id_estado', 8) // Estado RECEPCIONADO
            ->pluck('id_documento')
            ->unique()
            ->toArray();

        $query = Documento::with(['estado', 'areaRemitente', 'areaDestino', 'movimientos' => function ($q) use ($idArea) {
                // Cargar solo el movimiento de recepción de esta área para mostrar fecha
                $q->where('id_area_destino', $idArea)
                  ->where('id_estado', 8)
                  ->orderBy('au_fechacr', 'desc')
                  ->limit(1);
            }])
            ->where(function ($q) use ($idArea, $idsDocumentosRecepcionados) {
                // Documentos creados por el área y enviados a otros
                $q->where(function ($subQuery) use ($idArea) {
                    $subQuery->where('id_area_remitente', $idArea)
                        ->where('id_area_destino', '!=', $idArea);
                })
                // O documentos que recepcionó y luego derivó (ya no están en su poder)
                ->orWhere(function ($subQuery) use ($idArea, $idsDocumentosRecepcionados) {
                    $subQuery->whereIn('id_documento', $idsDocumentosRecepcionados)
                        ->where('id_area_destino', '!=', $idArea);
                });
            })
            ->orderBy('au_fechamd', 'desc'); // Ordenar por última modificación

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_documento', 'LIKE', "%$buscar%")
                    ->orWhere('expediente_documento', 'LIKE', "%$buscar%")
                    ->orWhere('folio_documento', 'LIKE', "%$buscar%")
                    ->orWhere('asunto_documento', 'LIKE', "%$buscar%");
            });
        }

        return $query->paginate(10);
    }

    /**
     * Cuenta los documentos pendientes en el área especificada
     */
    public function contarPendientesPorArea($idArea)
    {
        return Documento::where('id_area_destino', $idArea)
            ->whereHas('estado', function ($q) {

                $q->whereIn('nombre_estado', [
                    'DERIVADO',
                    'SUBSANADO',
                    'RETORNADO',
                    'PARA ARCHIVAR'
                ]);
            })
            ->count();
    }
}

