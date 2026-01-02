<?php

namespace App\Services\Documento;

use App\Models\ArchivoDocumento;
use App\Models\Documento;
use App\Models\Movimiento;
use App\Models\Transicion;
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

    public function listarPorAreaConFiltros(int $idArea, int $paginado = 10, ?string $buscar = null, ?string $fechaInicio = null, ?string $fechaFin = null, ?string $idEstado = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPaginadoPorAreaConFiltros($idArea, $paginado, $buscar, $fechaInicio, $fechaFin, $idEstado, $columnaOrden, $orden, $relaciones);
    }

    // Encontrar un documento por id
    public function obtenerPorId(int $id, array $relaciones = [])
    {
        return $this->repository->obtenerPorId($id, $relaciones);
    }

    public function obtenerPorIdParaArea(int $id, int $idAreaUsuario, array $relaciones = [], bool $incluirDerivaciones = false)
    {
        $documento = $this->repository->obtenerPorId($id, $relaciones);

        if ($documento && $documento->relationLoaded('archivos')) {
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

            // Solo incluir archivos de derivaciones cuando se pida explícitamente (Pendientes)
            if ($incluirDerivaciones && $documento->id_area_destino == $idAreaUsuario) {
                // Obtener TODAS las áreas que han participado en la derivación del documento
                $estadoDerivado = DB::table('ta_estado')->where('nombre_estado', 'DERIVADO')->first();
                $areasQueDerivaron = [];

                if ($estadoDerivado) {
                    $movimientosDerivacion = DB::table('ta_movimiento')
                        ->where('id_documento', $documento->id_documento)
                        ->where('id_estado', $estadoDerivado->id_estado)
                        ->whereNotNull('id_area_origen')
                        ->pluck('id_area_origen')
                        ->unique()
                        ->toArray();

                    $areasQueDerivaron = $movimientosDerivacion;
                }

                // Incluir los adjuntos de TODAS las áreas que han derivado el documento
                if (!empty($areasQueDerivaron)) {
                    $adjuntosExtra = $todosAdjuntos
                        ->filter(function ($archivo) use ($areasQueDerivaron) {
                            return in_array($archivo->id_area, $areasQueDerivaron);
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
            \App\Models\Movimiento::create([
                'id_documento'               => $documento->id_documento,
                'id_estado'                  => $estadoEnTramite->id_estado,
                'id_area_origen'             => $idAreaOrigen,  // De dónde viene
                'id_area_destino'            => $idAreaDestino, // Quién lo tiene ahora (YO)
                'observacion_doc_movimiento' => 'RECEPCIÓN: Documento puesto en trámite por ' . ($usuario->nombre_usuario ?? $usuario->name),
                'fecha_recepcion'            => Carbon::now()->format('Y-m-d H:i:s'),
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
            \App\Models\Movimiento::create([
                'id_documento'    => $idDocumento,
                'id_estado'       => $estadoDerivado->id_estado,
                'id_area_origen'  => $idAreaOrigen,  // DE
                'id_area_destino' => $idAreaDestino, // PARA
                'observacion_doc_movimiento' => $observaciones,
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

            // -----------------------------------------------------------------------
            // SOLUCIÓN AL ERROR: Detectar "RECEPCIONAR" o "RECEPCIONAR SUBSANACION"
            // -----------------------------------------------------------------------
            if (str_contains(strtoupper($transicion->evento_transicion), 'RECEPCIONAR')) {
                // 1. Asignar fecha para que aparezca en "Mis Documentos"
                $datosDocumento['fecha_recepcion_documento'] = Carbon::now()->format('Y-m-d H:i:s');

                // IMPORTANTE: NO limpiar observacion_documento porque es información original del documento
                // Las alertas rojas/verdes se controlan por el ESTADO, no borrando este campo
            }

            // Si la transición es ARCHIVADO, integrar archivos de otras áreas al remitente original
            if (strtoupper($transicion->evento_transicion) === 'ARCHIVADO') {
                $datosDocumento['fecha_despacho_documento'] = Carbon::now()->format('Y-m-d H:i:s');
                // IMPORTANTE: NO limpiar observacion_documento porque es información original del documento

                $idAreaRemitente = $documento->id_area_remitente;
                if ($idAreaRemitente) {
                    DB::table('ta_archivo_documento')
                        ->where('id_documento', $documento->id_documento)
                        ->whereNotNull('id_area')
                        ->where('id_area', '!=', $idAreaRemitente)
                        ->update(['id_area' => $idAreaRemitente]);
                }
            }

            // Lógica para movimientos que mueven el documento (DERIVAR, DEVOLVER, OBSERVAR, SUBSANADO)
            // En estos casos cambiamos el dueño (id_area_destino) y limpiamos la recepción
            $eventosDeDesplazamiento = ['DERIVAR', 'DEVOLVER', 'OBSERVAR', 'SUBSANADO'];

            if (in_array(strtoupper($transicion->evento_transicion), $eventosDeDesplazamiento) && isset($datos['id_area_destino'])) {
                $datosDocumento['id_area_destino'] = $datos['id_area_destino'];
                $datosDocumento['fecha_recepcion_documento'] = null; // Sale de mi bandeja
                $datosDocumento['fecha_emision_documento'] = Carbon::now();

                // IMPORTANTE: NO sobrescribir observacion_documento con el motivo de observación
                // La observación del documento es información original del documento
                // El motivo de la observación se guarda SOLO en ta_movimiento (observacion_doc_movimiento)
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

            // Definir Origen y Destino para el historial (Movimiento)
            $idAreaUsuario = $usuario->persona->id_area ?? null;

            if (in_array(strtoupper($transicion->evento_transicion), $eventosDeDesplazamiento)) {
                // Si yo lo envío, Origen soy YO, Destino es el OTRO
                $datosMovimiento['id_area_origen'] = $idAreaUsuario;
                $datosMovimiento['id_area_destino'] = $datos['id_area_destino'] ?? ($documento->id_area_destino);
            }
            elseif (str_contains(strtoupper($transicion->evento_transicion), 'RECEPCIONAR')) {
                // Si yo recepciono: Origen es quien lo envió (o el remitente original si viene de vuelta), Destino soy YO
                // Para "RECEPCIONAR SUBSANACION", el origen lógicamente es quien lo subsanó (que era el destino anterior)

                // Buscamos el último movimiento para saber quién lo tenía
                $ultimoMov = DB::table('ta_movimiento')
                                ->where('id_documento', $idDocumento)
                                ->orderByDesc('id_movimiento')
                                ->first();

                $datosMovimiento['id_area_origen'] = $ultimoMov ? $ultimoMov->id_area_destino : $documento->id_area_remitente;
                $datosMovimiento['id_area_destino'] = $idAreaUsuario;
                $datosMovimiento['fecha_recepcion'] = Carbon::now()->format('Y-m-d H:i:s');
            }
            else {
                // Casos por defecto
                $datosMovimiento['id_area_origen'] = $idAreaUsuario;
                $datosMovimiento['id_area_destino'] = $idAreaUsuario;
            }

            // Registrar el movimiento
            \App\Models\Movimiento::create($datosMovimiento);

            DB::commit();

            return $documento;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al procesar la transición: ' . $e->getMessage());
        }
    }
    /**
     * Obtener historial de documentos derivados por un área
     */
    public function obtenerHistorialDerivaciones(int $idArea, ?string $buscar = null)
    {
        // Identificar estados clave
        $estadoRecepcion = DB::table('ta_estado')
            ->where(function ($q) {
                $q->where('nombre_estado', 'LIKE', 'EN TRÁMITE')
                  ->orWhere('nombre_estado', 'LIKE', 'EN TRAMITE')
                  ->orWhere('nombre_estado', 'LIKE', 'RECEPCIONADO');
            })
            ->first();

        $estadoDerivado = DB::table('ta_estado')
            ->where('nombre_estado', 'DERIVADO')
            ->first();

        $idsEstadosMov = [];
        if ($estadoRecepcion) {
            $idsEstadosMov[] = $estadoRecepcion->id_estado;
        }
        if ($estadoDerivado) {
            $idsEstadosMov[] = $estadoDerivado->id_estado;
        }

        // Obtener IDs de documentos que el área ha recepcionado alguna vez
        $idsDocumentosRecepcionados = DB::table('ta_movimiento')
            ->where('id_area_destino', $idArea)
            ->whereIn('id_estado', $estadoRecepcion ? [$estadoRecepcion->id_estado] : [])
            ->pluck('id_documento')
            ->unique()
            ->toArray();

        $query = Documento::with([
                'estado',
                'areaRemitente',
                'areaDestino',
                'movimientos' => function ($q) use ($idArea, $idsEstadosMov, $estadoRecepcion, $estadoDerivado) {
                    $q->with('estado')
                        ->whereIn('id_estado', $idsEstadosMov)
                        ->where(function ($sub) use ($idArea, $estadoRecepcion, $estadoDerivado) {
                            if ($estadoRecepcion) {
                                $sub->orWhere(function ($qr) use ($idArea, $estadoRecepcion) {
                                    $qr->where('id_estado', $estadoRecepcion->id_estado)
                                       ->where('id_area_destino', $idArea);
                                });
                            }
                            if ($estadoDerivado) {
                                $sub->orWhere(function ($qr) use ($idArea, $estadoDerivado) {
                                    $qr->where('id_estado', $estadoDerivado->id_estado)
                                       ->where('id_area_origen', $idArea);
                                });
                            }
                        })
                        ->orderBy('au_fechacr', 'desc');
                }
            ])
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
     * Historial por área basado en movimientos (recepción y derivación).
     * Muestra entradas separadas por cada recepción o derivación hecha por el área.
     */
    public function obtenerHistorialMovimientosArea(int $idArea, ?string $buscar = null)
    {
        // Obtener TODOS los estados de recepción (puede haber múltiples)
        $idsRecepcion = DB::table('ta_estado')
            ->where(function ($q) {
                $q->where('nombre_estado', 'LIKE', '%TRÁMITE%')
                    ->orWhere('nombre_estado', 'LIKE', '%TRAMITE%')
                    ->orWhere('nombre_estado', '=', 'RECEPCIONADO');
            })
            ->pluck('id_estado')
            ->toArray();

        // Obtener estado DERIVADO
        $estadoDerivado = DB::table('ta_estado')
            ->where('nombre_estado', 'DERIVADO')
            ->first();

        $idDerivado = $estadoDerivado->id_estado ?? null;

        $query = Movimiento::with([
                'estado',
                'documento.estado',
                'documento.areaRemitente',
                'documento.areaDestino',
            ])
            ->where(function ($q) use ($idArea, $idsRecepcion, $idDerivado) {
                // Recepciones del área (debe haber al menos una condición base)
                if (!empty($idsRecepcion)) {
                    $q->where(function ($sub) use ($idArea, $idsRecepcion) {
                        $sub->where('id_area_destino', $idArea)
                            ->whereIn('id_estado', $idsRecepcion);
                    });
                }

                // Derivaciones hechas por el área
                if ($idDerivado) {
                    if (!empty($idsRecepcion)) {
                        $q->orWhere(function ($sub) use ($idArea, $idDerivado) {
                            $sub->where('id_area_origen', $idArea)
                                ->where('id_estado', $idDerivado);
                        });
                    } else {
                        // Si no hay recepciones, usar where en lugar de orWhere
                        $q->where(function ($sub) use ($idArea, $idDerivado) {
                            $sub->where('id_area_origen', $idArea)
                                ->where('id_estado', $idDerivado);
                        });
                    }
                }
            })
            ->orderBy('au_fechacr', 'desc');

        if ($buscar) {
            $query->whereHas('documento', function ($q) use ($buscar) {
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
            ->where(function($q) {
                // Documentos sin recepcionar O solicitudes de rectificación EN ESPERA (estado 10)
                $q->whereNull('fecha_recepcion_documento')
                  ->orWhere('id_estado', 10); // 10=Solicitud rectificación (aún sin resolver)
            })
            ->whereHas('estado', function ($q) {

                $q->whereIn('nombre_estado', [
                    'DERIVADO',
                    'SUBSANADO',
                    'RETORNADO',
                    'OBSERVADO',
                    'PARA ARCHIVAR'
                ])->orWhere('id_estado', 10); // 10=Solicitud rectificación
            })
            ->count();
    }

    /**
     * Registra una solicitud de rectificación pública (estado 10) desde "Consulta tu Trámite".
     */
    public function registrarSolicitudRectificacionPublica(Documento $documento, string $motivo, array $archivos = [])
    {
        DB::beginTransaction();

        try {
            $areaMesa = $documento->id_area_remitente; // Mesa de Partes (creador original)

            // Registrar movimiento: 10 = SOLICITAR RECTIFICACIÓN
            Movimiento::create([
                'id_documento' => $documento->id_documento,
                'id_estado' => 10,
                'id_area_origen' => $documento->id_area_destino,
                'id_area_destino' => $areaMesa,
                'observacion_doc_movimiento' => $motivo,
            ]);

            // Actualizar documento a estado 10 y asignar destino Mesa de Partes
            $this->repository->modificar([
                'id_estado' => 10,
                'id_area_destino' => $areaMesa,
                'fecha_recepcion_documento' => null,
            ], $documento);

            // Guardar archivos de evidencia si existen
            if (!empty($archivos)) {
                $archivoService = resolve(\App\Services\Documento\ArchivoDocumentoService::class);
                $archivosInfo = $archivoService->guardarMultiplesArchivos(
                    archivos: $archivos,
                    ruta: 'gestion/documentos/evidencias_rectificacion',
                    idDocumento: $documento->id_documento,
                    idArea: null // Sin área específica (solicitud pública)
                );

                foreach ($archivosInfo as $info) {
                    \App\Models\ArchivoDocumento::create(array_merge($info, [
                        'tipo_archivo' => 'evidencia_rectificacion'
                    ]));
                }
            }

            DB::commit();

            return $documento->refresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('No se pudo registrar la solicitud de rectificación: ' . $e->getMessage());
        }
    }

    /**
     * Resuelve la solicitud de rectificación desde Mesa de Partes.
     */
    public function resolverSolicitudRectificacion(Documento $documento, string $decision, ?string $motivo = null)
    {
        DB::beginTransaction();

        try {
            $decision = strtolower($decision);

            if (!in_array($decision, ['aceptar', 'rechazar'])) {
                throw new \Exception('Decisión de rectificación no válida');
            }

            $usuario = Auth::user();

            if ($decision === 'aceptar') {
                // Movimiento: 9 = POR RECTIFICAR (se acepta solicitud)
                Movimiento::create([
                    'id_documento' => $documento->id_documento,
                    'id_estado' => 9,
                    'id_area_origen' => $documento->id_area_destino,
                    'id_area_destino' => $documento->id_area_destino,
                    'observacion_doc_movimiento' => $motivo,
                ]);

                // Actualizar documento a POR RECTIFICAR
                $this->repository->modificar([
                    'id_estado' => 9,
                ], $documento);
            } else {
                // Movimiento: 11 = RECHAZAR RECTIFICACIÓN
                Movimiento::create([
                    'id_documento' => $documento->id_documento,
                    'id_estado' => 11,
                    'id_area_origen' => $documento->id_area_destino,
                    'id_area_destino' => $documento->id_area_destino,
                    'observacion_doc_movimiento' => $motivo,
                ]);

                // Actualizar documento a ARCHIVADO (6)
                $this->repository->modificar([
                    'id_estado' => 6,
                    'fecha_despacho_documento' => Carbon::now()->format('Y-m-d H:i:s'),
                ], $documento);
            }

            DB::commit();

            return $documento->refresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('No se pudo resolver la solicitud de rectificación: ' . $e->getMessage());
        }
    }

    /**
     * Marca la solicitud de rectificación (estado 10) como vista, registrando fecha_recepcion.
     */
    public function marcarSolicitudRectificacionVista(Documento $documento, int $idAreaDestino)
    {
        Movimiento::where('id_documento', $documento->id_documento)
            ->where('id_estado', 10)
            ->where('id_area_destino', $idAreaDestino)
            ->whereNull('fecha_recepcion')
            ->orderByDesc('au_fechacr')
            ->first()?->update(['fecha_recepcion' => Carbon::now()->format('Y-m-d H:i:s')]);
    }

    /**
     * Procesa la observación de un documento, cambiando su estado, área y guardando evidencias.
     */
    public function observar(int $idDocumento, int $idAreaDestino, string $motivo, array $archivosEvidencia = [])
    {
        DB::beginTransaction();

        try {
            $documento = $this->repository->obtenerPorId($idDocumento);

            if (!$documento) {
                throw new \Exception('Documento no encontrado.');
            }

            // 1. Buscar la transición OBSERVAR
            $transicion = Transicion::where('evento_transicion', 'OBSERVAR')
                ->where('id_estado_actual_transicion', $documento->id_estado)
                ->first();

            if (!$transicion) {
                // Fallback por seguridad
                $transicion = Transicion::where('evento_transicion', 'OBSERVAR')->first();
            }

            if (!$transicion) {
                throw new \Exception('No existe una transición de "OBSERVAR" configurada para el estado actual del documento.');
            }

            $this->procesarTransicion(
                $documento->id_documento,
                $transicion->id_transicion,
                [
                    'id_area_destino' => $idAreaDestino,
                    'observacion'     => $motivo
                ]
            );

            // 3. Guardar archivos de evidencia si existen
            if (!empty($archivosEvidencia)) {
                $archivoService = resolve(ArchivoDocumentoService::class);
                $idAreaUsuario = Auth::user()->persona->id_area ?? null;

                $archivosInfo = $archivoService->guardarMultiplesArchivos(
                    archivos: $archivosEvidencia,
                    ruta: 'gestion/documentos/evidencias_observacion',
                    idDocumento: $documento->id_documento,
                    idArea: $idAreaUsuario
                );

                foreach ($archivosInfo as $info) {
                    \App\Models\ArchivoDocumento::create(array_merge($info, [
                        'tipo_archivo' => 'evidencia_observacion'
                    ]));
                }
            }

            DB::commit();
            return $documento->refresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al observar el documento: ' . $e->getMessage());
        }
    }

}

