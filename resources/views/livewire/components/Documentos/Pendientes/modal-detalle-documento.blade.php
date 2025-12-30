@php
use Illuminate\Support\Facades\Storage;
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

                    <!-- INFORMACIÓN DEL DOCUMENTO -->
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

                    @php
                    $nombreEstado = strtoupper($modeloDocumento->estado->nombre_estado ?? '');
                    $esSolicitudRectificacion = ($modeloDocumento->id_estado == 10) || str_contains($nombreEstado, 'RECTIFIC');
                    $ultimoMovimiento = $modeloDocumento->movimientos()->latest('au_fechacr')->first();
                    $motivoRectificacion = $esSolicitudRectificacion && $ultimoMovimiento ? $ultimoMovimiento->observacion_doc_movimiento : null;
                    $movObservado = $modeloDocumento->movimientos()
                        ->whereHas('estado', function($q){ $q->where('nombre_estado','OBSERVADO'); })
                        ->latest('au_fechacr')
                        ->first();
                    $motivoObservacion = $movObservado?->observacion_doc_movimiento;
                    @endphp

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

                    @if($motivoObservacion)
                    @php
                        $nombreEstadoActual = strtoupper($modeloDocumento->estado->nombre_estado ?? '');
                        // Mostrar el check verde de subsanación si está en estado RECEPCION SUBSANADA o SUBSANADO
                        $esRecepcionSubsanada = $nombreEstadoActual === 'RECEPCION SUBSANADA' || $nombreEstadoActual === 'SUBSANADO';
                        $mostrarSubsanacion = $esRecepcionSubsanada;
                    @endphp
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

                        @if($mostrarSubsanacion)
                        <div class="alert alert-success d-flex align-items-center mt-3">
                            <i class="ki-outline ki-check-circle fs-2 me-3 text-success"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-success">✓ Observación subsanada</div>
                                <div class="text-gray-700 fs-7 mt-1">El documento ha sido corregido y está listo para ser recepcionado.</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($modeloDocumento->fecha_recepcion_documento)
                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Fecha recepción:</div>
                        <div class="text-gray-800">{{ \Carbon\Carbon::parse($modeloDocumento->fecha_recepcion_documento)->format('d/m/Y') }}</div>
                    </div>
                    @endif

                    <!-- ARCHIVOS ADJUNTOS -->
                    @if($modeloDocumento->archivos && count($modeloDocumento->archivos) > 0)
                    @php
                    $archivosOriginales = $modeloDocumento->archivos->where('tipo_archivo', 'original');
                    // En Pendientes siempre mostrar las evidencias para que Mesa de Partes las revise
                    $archivosEvidencia = $modeloDocumento->archivos->where('tipo_archivo', 'evidencia_rectificacion');
                    $archivosEvidenciaObservacion = $modeloDocumento->archivos->where('tipo_archivo', 'evidencia_observacion');
                    @endphp

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

                    @if($archivosEvidenciaObservacion->count() > 0)
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
                    @endif

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
