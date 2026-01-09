<div>
    <div wire:ignore.self class="modal fade" id="modal-detalle-documento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-800px">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0 justify-content-between">
                    <h2 class="fw-bold text-gray-800">Detalles del Documento</h2>
                    <div class="btn btn-icon btn-sm btn-light-primary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-5 mx-xl-10 pt-0 pb-10">
                    @if($modeloDocumento)
                    <div class="mb-10">
                        <div class="mb-5">
                            <div class="fw-bold text-gray-600 mb-1">Expediente:</div>
                            <div class="fw-bold text-gray-800 fs-4">{{ $modeloDocumento->expediente_documento }}</div>
                            @if($modeloDocumento->numero_documento)
                            <div class="text-muted fs-7">Número: {{ $modeloDocumento->numero_documento }}</div>
                            @endif
                        </div>

                        <div class="mb-5">
                            <div class="fw-bold text-gray-600 mb-1">Asunto:</div>
                            <div class="text-gray-800">{{ $modeloDocumento->asunto_documento }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="fw-bold text-gray-600 mb-1">Observación:</div>
                            <div class="text-gray-800 text-uppercase">
                                {{ $modeloDocumento->observacion_documento }}
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-600 mb-1">Folio:</div>
                                <div class="text-gray-800">{{ $modeloDocumento->folio_documento }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-600 mb-1">Tipo de Documento:</div>
                                <div class="text-gray-800">{{ $modeloDocumento->tipoDocumento->nombre_catalogo ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-600 mb-1">Área Remitente:</div>
                                <div class="text-gray-800">{{ $modeloDocumento->areaRemitente->nombre_area ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-600 mb-1">Área Destino:</div>
                                <div class="text-gray-800">{{ $modeloDocumento->areaDestino->nombre_area ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-600 mb-1">Fecha de Recepción:</div>
                                <div class="text-gray-800">{{ formatoFechaText($modeloDocumento->fecha_recepcion_documento) }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-600 mb-1">Fecha de Registro:</div>
                                <div class="text-gray-800">{{ formatoFechaText($modeloDocumento->au_fechacr) }}</div>
                            </div>
                        </div>

                        @if($modeloDocumento->archivos && $modeloDocumento->archivos->count() > 0)
                        <div class="mb-3">
                            <div class="fw-bold text-gray-600 mb-3">Archivos Adjuntos:</div>
                            <div class="row g-3">
                                @foreach($modeloDocumento->archivos as $archivo)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border border-gray-300">
                                        <div class="card-body d-flex flex-column p-4">
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
                                <span class="badge badge-light-{{ $colorEstado }} py-3 px-4 fs-6">
                                    {{ $modeloDocumento->estado->nombre_estado }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
