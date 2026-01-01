@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
@endphp

<div wire:ignore.self class="modal fade" id="modal-detalle-documento" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">
                    Detalle del documento
                </h3>

                <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body px-5">
                <div class="d-flex flex-column px-5 px-lg-10">

                    @if ($modeloDocumento)

                    <div class="fw-bold text-dark mb-3 mt-3">
                        <i class="ki-outline ki-document me-2"></i> Información del documento
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="fw-bold text-gray-600 mb-1">Número documento:</div>
                            <div class="text-gray-800">{{ $modeloDocumento->numero_documento }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="fw-bold text-gray-600 mb-1">Folio:</div>
                            <div class="text-gray-800">{{ $modeloDocumento->folio_documento }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Tipo de documento:</div>
                        <div class="text-gray-800">{{ $modeloDocumento->tipoDocumento->descripcion_catalogo ?? 'N/A' }}</div>
                    </div>

                    {{-- REMITENTE INICIAL (Solicitado anteriormente) --}}
                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Área Remitente (Inicial):</div>
                        <div class="text-gray-800 fw-bold">
                            {{ $modeloDocumento->areaRemitente->nombre_area ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Asunto:</div>
                        <div class="text-gray-800">{{ $modeloDocumento->asunto_documento }}</div>
                    </div>

                    @if($modeloDocumento->observacion_documento)
                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Observación:</div>
                        <div class="text-gray-800 text-uppercase">
                            {{ $modeloDocumento->observacion_documento }}
                        </div>
                    </div>
                    @endif

                    {{-- ======================================================================== --}}
                    {{-- LÓGICA DE ESTADOS Y VISIBILIDAD DE ALERTAS --}}
                    {{-- ======================================================================== --}}
                    @php
                    // 1. Datos básicos
                    $nombreEstadoActual = strtoupper($modeloDocumento->estado->nombre_estado ?? '');
                    $idAreaActualUsuario = Auth::user()->persona->id_area ?? null;

                    // 2. Lógica para RECTIFICACIÓN (Mesa de Partes - Público)
                    $esSolicitudRectificacion = ($modeloDocumento->id_estado == 10) || str_contains($nombreEstadoActual, 'RECTIFIC');
                    $ultimoMovimiento = $modeloDocumento->movimientos()->latest('au_fechacr')->first();
                    $motivoRectificacion = $esSolicitudRectificacion && $ultimoMovimiento ? $ultimoMovimiento->observacion_doc_movimiento : null;

                    // 3. Lógica para OBSERVACIÓN (Áreas Internas)
                    $movObservado = $modeloDocumento->movimientos()
                    ->whereHas('estado', function($q){ $q->where('nombre_estado','OBSERVADO'); })
                    ->latest('au_fechacr')
                    ->first();

                    $motivoObservacion = $movObservado?->observacion_doc_movimiento;
                    $idAreaQueObservo = $movObservado?->id_area_origen;

                    // ----------------------------------------------------------------------
                    // CORRECCIÓN SOLICITADA: VISIBILIDAD DE ALERTA ROJA
                    // ----------------------------------------------------------------------
                    $esPendientes = request()->routeIs('documentos.pendientes.*'); // ¿Estamos en la vista de pendientes?
                    $estaRecepcionado = $modeloDocumento->fecha_recepcion_documento != null;
                    $esEstadoObservado = ($nombreEstadoActual === 'OBSERVADO');

                    $mostrarAlertaRoja = false;

                    if ($esEstadoObservado) {
                    if ($esPendientes) {
                    // En Pendientes: SIEMPRE mostrar para saber qué nos observaron
                    $mostrarAlertaRoja = true;
                    } else {
                    // En Mis Documentos: SOLO mostrar si ya le dimos "Recepcionar"
                    if ($estaRecepcionado) {
                    $mostrarAlertaRoja = true;
                    }
                    }
                    }

                    // VERDE: Check de subsanación
                    $estaSubsanado = ($nombreEstadoActual === 'SUBSANADO' || $nombreEstadoActual === 'RECEPCION SUBSANADA');
                    $soyElObservador = ($idAreaActualUsuario == $idAreaQueObservo);
                    $mostrarSubsanacion = $estaSubsanado && $soyElObservador;
                    @endphp

                    {{-- 1. BLOQUE DE RECTIFICACIÓN (SOLO MESA DE PARTES) --}}
                    @if($motivoRectificacion)
                    <div class="mb-3">
                        <div class="separator my-4"></div>
                        <div class="fw-bold text-dark mb-3">
                            <i class="ki-outline ki-information fs-3 me-2 text-warning"></i> Motivo de Rectificación
                        </div>
                        <div class="alert alert-warning d-flex align-items-start">
                            <i class="ki-outline ki-message-text fs-2 me-3"></i>
                            <div class="flex-grow-1">
                                <div class="text-gray-800 text-break">{{ $motivoRectificacion }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- 2. BLOQUE DE OBSERVACIÓN (ÁREAS INTERNAS) --}}

                    {{-- CASO A: ALERTA ROJA (Controlado por la nueva lógica) --}}
                    @if($mostrarAlertaRoja && $motivoObservacion)
                    <div class="mb-3">
                        <div class="separator my-4"></div>
                        <div class="fw-bold text-dark mb-3">
                            <i class="ki-outline ki-eye-slash fs-3 me-2 text-danger"></i> Motivo de Observación
                        </div>
                        <div class="alert alert-danger d-flex align-items-start">
                            <i class="ki-outline ki-message-text fs-2 me-3"></i>
                            <div class="flex-grow-1">
                                <div class="text-gray-800 text-break">{{ $motivoObservacion }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- CASO B: Check VERDE de subsanación --}}
                    @if($mostrarSubsanacion)
                    <div class="mb-3">
                        <div class="separator my-4"></div>
                        <div class="alert alert-success d-flex align-items-center mt-3">
                            <i class="ki-outline ki-check-circle fs-2 me-3 text-success"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-success">✓ Observación subsanada</div>
                                <div class="text-gray-700 fs-7 mt-1">El documento ha sido corregido y está listo para ser recepcionado.</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($modeloDocumento->fecha_recepcion_documento)
                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Fecha recepción:</div>
                        <div class="text-gray-800">{{ Carbon::parse($modeloDocumento->fecha_recepcion_documento)->format('d/m/Y') }}</div>
                    </div>
                    @endif

                    @if($modeloDocumento->archivos && count($modeloDocumento->archivos) > 0)
                    @php
                    // 1. Archivos Originales
                    $archivosOriginales = $modeloDocumento->archivos->where('tipo_archivo', 'original');

                    // 2. Evidencias de Rectificación
                    $archivosEvidencia = $modeloDocumento->archivos->where('tipo_archivo', 'evidencia_rectificacion');

                    // 3. Evidencias de Observación
                    $archivosEvidenciaObservacion = $modeloDocumento->archivos->where('tipo_archivo', 'evidencia_observacion');

                    // 4. OTROS (La solución a tu problema):
                    // Obtenemos los IDs de los que ya clasificamos arriba
                    $idsClasificados = $archivosOriginales->pluck('id_archivo_documento')
                    ->merge($archivosEvidencia->pluck('id_archivo_documento'))
                    ->merge($archivosEvidenciaObservacion->pluck('id_archivo_documento'))
                    ->toArray();

                    // Filtramos cualquier archivo que NO esté en los grupos anteriores (nulos, anexos, etc.)
                    $archivosOtros = $modeloDocumento->archivos->whereNotIn('id_archivo_documento', $idsClasificados);
                    @endphp

                    {{-- ARCHIVOS ORIGINALES --}}
                    @if($archivosOriginales->count() > 0)
                    <div class="mb-3">
                        <div class="separator my-4"></div>
                        <div class="fw-bold text-dark mb-3">
                            <i class="ki-outline ki-file-check fs-3 me-2 text-success"></i> Archivos adjuntos ({{ $archivosOriginales->count() }})
                        </div>
                        <div class="row g-3">
                            @foreach($archivosOriginales as $archivo)
                            <div class="col-md-6 col-lg-4" wire:key="archivo-{{ $archivo->id_archivo_documento }}">
                                <div class="card shadow-sm border border-gray-300 h-100">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-50px me-3">
                                                <span class="symbol-label bg-light-{{ $archivo->color }}">
                                                    <i class="ki-outline {{ $archivo->icono }} fs-2x text-{{ $archivo->color }}"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="fw-bold text-gray-800 text-truncate" title="{{ $archivo->nombre_original }}">
                                                    {{ Str::limit($archivo->nombre_original, 20) }}
                                                </div>
                                                <div class="text-muted fs-7">
                                                    {{ $archivo->tamanio_formateado }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 mt-auto">
                                            <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-sm btn-light-success flex-fill">
                                                <i class="ki-outline ki-eye fs-5"></i> Ver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- EVIDENCIA DE RECTIFICACIÓN --}}
                    @if($archivosEvidencia->count() > 0)
                    <div class="mb-3">
                        <div class="separator my-4"></div>
                        <div class="fw-bold text-dark mb-3">
                            <i class="ki-outline ki-document-text fs-3 me-2 text-warning"></i> Evidencia de rectificación ({{ $archivosEvidencia->count() }})
                        </div>
                        <div class="row g-3">
                            @foreach($archivosEvidencia as $archivo)
                            <div class="col-md-6 col-lg-4" wire:key="archivo-evidencia-{{ $archivo->id_archivo_documento }}">
                                <div class="card shadow-sm border border-warning h-100">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-50px me-3">
                                                <span class="symbol-label bg-light-{{ $archivo->color }}">
                                                    <i class="ki-outline {{ $archivo->icono }} fs-2x text-{{ $archivo->color }}"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="fw-bold text-gray-800 text-truncate" title="{{ $archivo->nombre_original }}">
                                                    {{ Str::limit($archivo->nombre_original, 20) }}
                                                </div>
                                                <div class="text-muted fs-7">
                                                    {{ $archivo->tamanio_formateado }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 mt-auto">
                                            <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-sm btn-light-warning flex-fill">
                                                <i class="ki-outline ki-eye fs-5"></i> Ver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- EVIDENCIA DE OBSERVACIÓN (Controlado por la nueva lógica) --}}
                    {{-- Se muestra si hay alerta roja (obs) o alerta verde (subsanado) visible --}}
                    @if(($mostrarAlertaRoja || $mostrarSubsanacion) && $archivosEvidenciaObservacion->count() > 0)
                    <div class="mb-3">
                        <div class="separator my-4"></div>
                        <div class="fw-bold text-dark mb-3">
                            <i class="ki-outline ki-document-text fs-3 me-2 text-danger"></i> Evidencia de observación ({{ $archivosEvidenciaObservacion->count() }})
                        </div>
                        <div class="row g-3">
                            @foreach($archivosEvidenciaObservacion as $archivo)
                            <div class="col-md-6 col-lg-4" wire:key="archivo-evidencia-observacion-{{ $archivo->id_archivo_documento }}">
                                <div class="card shadow-sm border border-danger h-100">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-50px me-3">
                                                <span class="symbol-label bg-light-{{ $archivo->color }}">
                                                    <i class="ki-outline {{ $archivo->icono }} fs-2x text-{{ $archivo->color }}"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="fw-bold text-gray-800 text-truncate" title="{{ $archivo->nombre_original }}">
                                                    {{ Str::limit($archivo->nombre_original, 20) }}
                                                </div>
                                                <div class="text-muted fs-7">
                                                    {{ $archivo->tamanio_formateado }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 mt-auto">
                                            <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-sm btn-light-danger flex-fill">
                                                <i class="ki-outline ki-eye fs-5"></i> Ver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ARCHIVOS ADICIONALES / ANEXOS (Agregados por áreas) --}}
                    @if($archivosOtros->count() > 0)
                    <div class="mb-3">
                        <div class="separator my-4"></div>
                        <div class="fw-bold text-dark mb-3">
                            <i class="ki-outline ki-file-added fs-3 me-2 text-primary"></i> Archivos adicionales ({{ $archivosOtros->count() }})
                        </div>
                        <div class="row g-3">
                            @foreach($archivosOtros as $archivo)
                            <div class="col-md-6 col-lg-4" wire:key="archivo-otros-{{ $archivo->id_archivo_documento }}">
                                <div class="card shadow-sm border border-primary h-100">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-50px me-3">
                                                <span class="symbol-label bg-light-{{ $archivo->color }}">
                                                    <i class="ki-outline {{ $archivo->icono }} fs-2x text-{{ $archivo->color }}"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="fw-bold text-gray-800 text-truncate" title="{{ $archivo->nombre_original }}">
                                                    {{ Str::limit($archivo->nombre_original, 20) }}
                                                </div>
                                                <div class="text-muted fs-7">
                                                    {{ $archivo->tamanio_formateado }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 mt-auto">
                                            <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-sm btn-light-primary flex-fill">
                                                <i class="ki-outline ki-eye fs-5"></i> Ver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endif

                    {{-- ESTADO ACTUAL --}}
                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Estado:</div>
                        <div>
                            @if($modeloDocumento->estado)
                            @php
                            $nombreEstado = strtoupper($modeloDocumento->estado->nombre_estado);
                            $colorEstado = match($nombreEstado) {
                            'RECEPCIONADO' => 'success',
                            'OBSERVADO' => 'danger',
                            'DERIVADO' => 'secondary',
                            'ARCHIVADO' => 'primary',
                            'SUBSANADO' => 'success',
                            'RECEPCION SUBSANADA' => 'success',
                            default => 'info'
                            };
                            @endphp
                            <span class="badge badge-light-{{ $colorEstado }} py-2 px-3">
                                {{ $modeloDocumento->estado->nombre_estado }}
                            </span>
                            @else
                            <span class="badge badge-light-secondary py-2 px-3">Sin estado</span>
                            @endif
                        </div>
                    </div>

                    @endif

                </div>
            </div>

            <div class="modal-footer flex-center border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
