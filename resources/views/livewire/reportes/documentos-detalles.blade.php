<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Detalle del Documento">
        <x-breadcrumb.item titulo="Reportes" enlace="{{ route('reportes.index') }}" />
        <x-breadcrumb.item titulo="Reporte de Documentos" enlace="{{ route('reportes.documentos') }}" />
        <x-breadcrumb.item titulo="Detalle" />
    </x-breadcrumb>
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-document me-2"></i>
                        Detalle del Documento: {{ $documento->numero_documento }}
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('reportes.documentos') }}" class="btn btn-sm btn-light">
                            <i class="ki-outline ki-arrow-left fs-4 me-1"></i>
                            Volver al Reporte
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información General -->
                    <div class="card bg-light-primary border-0 mb-6">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <span class="text-muted fw-semibold d-block fs-7 mb-2">Número de Documento</span>
                                    <span class="text-gray-800 fw-bold fs-5">{{ $documento->numero_documento }}</span>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <span class="text-muted fw-semibold d-block fs-7 mb-2">Expediente</span>
                                    <span class="text-gray-800 fw-bold fs-5">{{ $documento->expediente_documento ?? 'S/N' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted fw-semibold d-block fs-7 mb-2">Tipo de Documento</span>
                                    <span class="badge badge-light-info fs-6 fw-bold">{{ $documento->tipoDocumento->descripcion_catalogo ?? 'N/A' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted fw-semibold d-block fs-7 mb-2">Estado Actual</span>
                                    @php
                                        $estado = strtoupper($documento->estado->nombre_estado ?? 'N/A');
                                        $color = match(true) {
                                            str_contains($estado, 'RECEPCIONADO') || str_contains($estado, 'FINALIZADO') => 'success',
                                            str_contains($estado, 'PENDIENTE') => 'warning',
                                            str_contains($estado, 'OBSERVADO') => 'danger',
                                            str_contains($estado, 'ARCHIVADO') => 'secondary',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <span class="badge badge-light-{{ $color }} fs-6 fw-bold">{{ $estado }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-6 mb-6">
                        <!-- Columna Izquierda -->
                        <div class="col-md-6">
                            <!-- Asunto -->
                            <div class="card border border-gray-300 mb-5">
                                <div class="card-body">
                                    <h4 class="card-title mb-3">
                                        <i class="ki-outline ki-note-2 me-2 text-primary"></i>
                                        Asunto
                                    </h4>
                                    <p class="text-gray-800 fw-medium mb-0">{{ $documento->asunto_documento }}</p>
                                </div>
                            </div>

                            <!-- Remitente y Fechas -->
                            <div class="card border border-gray-300 mb-5">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">
                                        <i class="ki-outline ki-user me-2 text-primary"></i>
                                        Remitente y Fechas
                                    </h4>
                                    <div class="mb-4">
                                        <span class="text-muted fw-semibold d-block fs-7 mb-2">Remitente</span>
                                        <span class="text-gray-800 fw-medium fs-6">{{ $documento->remitente ?? 'N/A' }}</span>
                                    </div>
                                    <div class="mb-4">
                                        <span class="text-muted fw-semibold d-block fs-7 mb-2">Fecha de Creación</span>
                                        <span class="text-gray-800 fw-medium fs-6">
                                            <i class="ki-outline ki-calendar me-1"></i>
                                            {{ Carbon\Carbon::parse($documento->au_fechacr)->format('d/m/Y H:i:s') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-muted fw-semibold d-block fs-7 mb-2">Tiempo Transcurrido</span>
                                        <span class="badge badge-light-info fs-7">
                                            <i class="ki-outline ki-time me-1"></i>
                                            {{ Carbon\Carbon::parse($documento->au_fechacr)->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Folios y Archivos -->
                            <div class="card border border-gray-300">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">
                                        <i class="ki-outline ki-document me-2 text-primary"></i>
                                        Documentación
                                    </h4>
                                    @if($documento->folio_documento)
                                    <div class="mb-4">
                                        <span class="text-muted fw-semibold d-block fs-7 mb-2">Número de Folios</span>
                                        <span class="badge badge-outline badge-secondary fs-6">
                                            <i class="ki-outline ki-book-open me-1"></i> {{ $documento->folio_documento }} Folios
                                        </span>
                                    </div>
                                    @endif
                                    @if($documento->archivos && count($documento->archivos) > 0)
                                    <div>
                                        <span class="text-muted fw-semibold d-block fs-7 mb-3">Archivos Adjuntos ({{ count($documento->archivos) }})</span>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($documento->archivos as $archivo)
                                            <a href="{{ route("archivo.ver", ["path" => $archivo->ruta_archivo]) }}" target="_blank" class="d-flex align-items-center p-3 border border-gray-300 rounded bg-hover-light">
                                                <i class="ki-outline ki-file fs-2x text-primary me-3"></i>
                                                <div class="flex-grow-1">
                                                    <span class="text-gray-800 fw-semibold d-block">{{ $archivo->nombre_archivo }}</span>
                                                    <span class="text-muted fs-8">{{ $archivo->tipo_archivo ?? 'Archivo' }}</span>
                                                </div>
                                                <i class="ki-outline ki-down-square fs-2x text-primary"></i>
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                    @else
                                    <div class="text-center py-4">
                                        <i class="ki-outline ki-document fs-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Sin archivos adjuntos</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="col-md-6">
                            <!-- Flujo de Áreas -->
                            <div class="card border border-gray-300 mb-5">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">
                                        <i class="ki-outline ki-arrows-circle me-2 text-primary"></i>
                                        Flujo del Documento
                                    </h4>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="symbol symbol-50px me-4">
                                                <span class="symbol-label bg-light-danger">
                                                    <i class="ki-outline ki-geolocation fs-2x text-danger"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-muted fw-semibold d-block fs-8 mb-1">Área de Origen</span>
                                                <span class="text-gray-800 fw-bold fs-6">{{ $documento->areaRemitente->nombre_area ?? 'Externo' }}</span>
                                            </div>
                                        </div>

                                        <div class="separator separator-dashed my-4"></div>

                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-50px me-4">
                                                <span class="symbol-label bg-light-success">
                                                    <i class="ki-outline ki-delivery-time fs-2x text-success"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-muted fw-semibold d-block fs-8 mb-1">Área de Destino</span>
                                                <span class="text-gray-800 fw-bold fs-6">{{ $documento->areaDestino->nombre_area ?? 'Sin asignar' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Historial de Movimientos -->
                            <div class="card border border-gray-300">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">
                                        <i class="ki-outline ki-directbox-default me-2 text-primary"></i>
                                        Historial de Movimientos
                                    </h4>
                                    @if($documento->movimientos && count($documento->movimientos) > 0)
                                    <div class="timeline timeline-border-dashed">
                                        @foreach($documento->movimientos as $index => $movimiento)
                                        <div class="timeline-item">
                                            <div class="timeline-line w-40px"></div>
                                            <div class="timeline-icon symbol symbol-circle symbol-40px">
                                                <div class="symbol-label bg-light-{{ $index == 0 ? 'success' : 'primary' }}">
                                                    <i class="ki-outline ki-{{ $index == 0 ? 'check' : 'arrow-right' }} fs-2 text-{{ $index == 0 ? 'success' : 'primary' }}"></i>
                                                </div>
                                            </div>
                                            <div class="timeline-content mb-7 mt-n1">
                                                <div class="pe-3 mb-3">
                                                    <div class="fs-6 fw-bold text-gray-800 mb-2">
                                                        {{ $movimiento->areaDestino->nombre_area ?? 'N/A' }}
                                                    </div>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <span class="badge badge-light-{{ $index == 0 ? 'success' : 'info' }} fs-8">
                                                            {{ $movimiento->estado->nombre_estado ?? 'Movimiento' }}
                                                        </span>
                                                    </div>
                                                    @if($movimiento->observacion_doc_movimiento)
                                                    <div class="text-muted fs-7 mb-2">
                                                        <i class="ki-outline ki-message-text-2 me-1"></i>
                                                        {{ $movimiento->observacion_doc_movimiento }}
                                                    </div>
                                                    @endif
                                                    <div class="text-gray-600 fs-8">
                                                        <i class="ki-outline ki-geolocation me-1"></i>
                                                        Desde: {{ $movimiento->areaOrigen->nombre_area ?? 'Externo' }}
                                                    </div>
                                                </div>
                                                <div class="overflow-auto pb-3">
                                                    <div class="d-flex align-items-center text-muted fs-8">
                                                        <i class="ki-outline ki-calendar-8 me-1"></i>
                                                        {{ Carbon\Carbon::parse($movimiento->au_fechacr)->format('d/m/Y H:i:s') }}
                                                        <span class="ms-2 text-primary">({{ Carbon\Carbon::parse($movimiento->au_fechacr)->diffForHumans() }})</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="text-center py-6">
                                        <div class="symbol symbol-70px mb-4 mx-auto">
                                            <span class="symbol-label bg-light-primary">
                                                <i class="ki-outline ki-information fs-2x text-primary"></i>
                                            </span>
                                        </div>
                                        <p class="text-muted fw-semibold mb-0">No hay movimientos registrados</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

