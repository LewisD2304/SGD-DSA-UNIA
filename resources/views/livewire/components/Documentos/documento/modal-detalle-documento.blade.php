@php
use Illuminate\Support\Facades\Storage;
@endphp

<div wire:ignore.self class="modal fade" id="modal-detalle-documento" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">

            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold text-gray-900 m-0">
                    <i class="ki-outline ki-book-open fs-2 me-2 text-primary"></i> Detalle del Documento
                </h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body pt-5 pb-10 px-lg-10">
                @if ($modeloDocumento)

                <div class="d-flex justify-content-between align-items-center bg-light-primary rounded p-4 mb-6 border border-primary border-dashed border-opacity-25">
                    <div class="d-flex flex-column">
                        <span class="text-gray-500 fw-semibold fs-7 text-uppercase mb-1">N° de Documento</span>
                        <span class="text-gray-900 fw-bold fs-3">{{ $modeloDocumento->numero_documento }}</span>
                    </div>

                    <div>
                        @if($modeloDocumento->estado)
                            @php
                                $nombreEstado = strtoupper($modeloDocumento->estado->nombre_estado);
                                $colorEstado = match($nombreEstado) {
                                    'RECEPCIONADO' => 'success',
                                    'OBSERVADO' => 'danger',
                                    'DERIVADO' => 'info', // Cambié a info/azul para derivado
                                    'ARCHIVADO' => 'dark', // Dark o secondary para archivado
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge badge-light-{{ $colorEstado }} fs-6 fw-bold py-3 px-4">
                                {{ $modeloDocumento->estado->nombre_estado }}
                            </span>
                        @else
                            <span class="badge badge-light-secondary fs-7">Sin estado</span>
                        @endif
                    </div>
                </div>

                <div class="row g-5 mb-6">
                    <div class="col-md-6">
                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6">
                            <div class="fw-semibold text-gray-400 fs-7">Folio</div>
                            <div class="d-flex align-items-center">
                                <i class="ki-outline ki-copy fs-3 text-gray-500 me-2"></i>
                                <div class="fs-6 fw-bold text-gray-800">{{ $modeloDocumento->folio_documento }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4">
                            <div class="fw-semibold text-gray-400 fs-7">Tipo de Documento</div>
                            <div class="d-flex align-items-center">
                                <i class="ki-outline ki-file fs-3 text-gray-500 me-2"></i>
                                <div class="fs-6 fw-bold text-gray-800">{{ $modeloDocumento->tipoDocumento->descripcion_catalogo ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($modeloDocumento->fecha_recepcion_documento)
                    <div class="col-md-6">
                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6">
                            <div class="fw-semibold text-gray-400 fs-7">Fecha Recepción</div>
                            <div class="fs-6 fw-bold text-gray-800">
                                <i class="ki-outline ki-calendar-tick fs-4 text-success me-1"></i>
                                {{ formatoFechaText($modeloDocumento->fecha_recepcion_documento) }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @php
                        $fechaArchivado = $modeloDocumento->fecha_despacho_documento;
                        if (!$fechaArchivado && optional($modeloDocumento->estado)->nombre_estado == 'ARCHIVADO') {
                            $fechaArchivado = $modeloDocumento->au_fechamd ?? $modeloDocumento->updated_at;
                        }
                    @endphp

                    @if($fechaArchivado && optional($modeloDocumento->estado)->nombre_estado == 'ARCHIVADO')
                    <div class="col-md-6">
                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4">
                            <div class="fw-semibold text-gray-400 fs-7">Fecha Archivadado</div>
                            <div class="fs-6 fw-bold text-gray-800">
                                <i class="ki-outline ki-archive fs-4 text-dark me-1"></i>
                                {{ formatoFechaText($fechaArchivado) }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mb-6">
                    <label class="fw-semibold text-gray-500 fs-7 mb-2">Asunto del Documento</label>
                    <div class="bg-light rounded p-4 border border-gray-200">
                        <p class="text-gray-800 fw-bold fs-6 m-0 lh-base">
                            {{ $modeloDocumento->asunto_documento }}
                        </p>
                    </div>
                </div>

                @if($modeloDocumento->observacion_documento)
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mb-6">
                    <i class="ki-outline ki-information-5 fs-2tx text-warning me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Observación</h4>
                            <div class="fs-6 text-gray-700 text-uppercase">
                                {{ $modeloDocumento->observacion_documento }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @php
                $nombreEstadoDoc = strtoupper($modeloDocumento->estado->nombre_estado ?? '');
                $esRectificacionAceptada = ($modeloDocumento->id_estado == 9) || str_contains($nombreEstadoDoc, 'POR RECTIFICAR');

                // Buscar el motivo: primero en movimiento estado 10 (solicitud), luego en estado 9 (aceptación)
                $movimientoConMotivo = $esRectificacionAceptada ? $modeloDocumento->movimientos()
                    ->whereIn('id_estado', [10, 9])
                    ->whereNotNull('observacion_doc_movimiento')
                    ->orderByDesc('au_fechacr')
                    ->first() : null;

                $motivoRectificacion = $movimientoConMotivo ? $movimientoConMotivo->observacion_doc_movimiento : null;
                @endphp

                @if($motivoRectificacion)
                <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-4 mb-6">
                    <i class="ki-outline ki-message-text fs-2tx text-info me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold mb-2">
                                <i class="ki-outline ki-information-2 fs-4 me-1"></i> Motivo de Rectificación
                            </h4>
                            <div class="fs-6 text-gray-800 lh-base" style="white-space: pre-wrap; word-wrap: break-word;">
                                {{ $motivoRectificacion }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($modeloDocumento->archivos && count($modeloDocumento->archivos) > 0)
                    @php
                    $archivosOriginales = $modeloDocumento->archivos->where('tipo_archivo', 'original');
                    // Solo mostrar evidencias si la solicitud fue aceptada (estado 9 = POR RECTIFICAR o superior)
                    $archivosEvidencia = ($modeloDocumento->id_estado != 10)
                        ? $modeloDocumento->archivos->where('tipo_archivo', 'evidencia_rectificacion')
                        : collect([]);
                    @endphp

                    @if($archivosOriginales->count() > 0)
                    <div class="separator separator-dashed my-6"></div>
                    <div class="mb-6">
                        <h4 class="fs-6 fw-bold text-gray-800 mb-4">
                            <i class="ki-outline ki-paper-clip fs-4 me-1"></i> Archivos Adjuntos ({{ $archivosOriginales->count() }})
                        </h4>

                        <div class="row g-4">
                            @foreach($archivosOriginales as $archivo)
                                <div class="col-md-6" wire:key="archivo-{{ $archivo->id_archivo_documento }}">
                                    <div class="d-flex align-items-center border border-dashed border-gray-300 rounded p-3 bg-white h-100 hover-elevate-up transition-300">
                                        <div class="symbol symbol-45px me-4">
                                            <span class="symbol-label bg-light-{{ $archivo->color }}">
                                                <i class="ki-outline {{ $archivo->icono }} fs-2x text-{{ $archivo->color }}"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                                            <span class="text-gray-800 fw-bold fs-6 text-truncate" title="{{ $archivo->nombre_original }}">
                                                {{ Str::limit($archivo->nombre_original, 25) }}
                                            </span>
                                            <span class="text-gray-400 fw-semibold fs-8">{{ $archivo->tamanio_formateado }}</span>
                                        </div>
                                        <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-icon btn-sm btn-light-primary ms-2" data-bs-toggle="tooltip" title="Ver Archivo">
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($archivosEvidencia->count() > 0)
                    <div class="separator separator-dashed my-6"></div>
                    <div class="mb-0">
                        <h4 class="fs-6 fw-bold text-gray-800 mb-4">
                            <i class="ki-outline ki-document-text fs-4 me-1 text-warning"></i> Evidencia de rectificación ({{ $archivosEvidencia->count() }})
                        </h4>

                        <div class="row g-4">
                            @foreach($archivosEvidencia as $archivo)
                                <div class="col-md-6" wire:key="archivo-evidencia-{{ $archivo->id_archivo_documento }}">
                                    <div class="d-flex align-items-center border border-dashed border-warning rounded p-3 bg-light-warning h-100 hover-elevate-up transition-300">
                                        <div class="symbol symbol-45px me-4">
                                            <span class="symbol-label bg-light-{{ $archivo->color }}">
                                                <i class="ki-outline {{ $archivo->icono }} fs-2x text-{{ $archivo->color }}"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                                            <span class="text-gray-800 fw-bold fs-6 text-truncate" title="{{ $archivo->nombre_original }}">
                                                {{ Str::limit($archivo->nombre_original, 25) }}
                                            </span>
                                            <span class="text-gray-600 fw-semibold fs-8">{{ $archivo->tamanio_formateado }}</span>
                                        </div>
                                        <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-icon btn-sm btn-warning ms-2" data-bs-toggle="tooltip" title="Ver Archivo">
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif

                @endif
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>
