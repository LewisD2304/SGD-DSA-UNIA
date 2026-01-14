<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Reporte de Documentos">
        <x-breadcrumb.item titulo="Reportes" enlace="{{ route('reportes.index') }}" />
        <x-breadcrumb.item titulo="Reporte de Documentos" />
    </x-breadcrumb>
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-document fs-2 me-2"></i>
                        Reporte de Documentos
                    </h3>
                    <div class="card-toolbar">
                        <div class="d-flex gap-2">
                            <a href="{{ route('reportes.index') }}" class="btn btn-sm btn-light">
                                <i class="ki-outline ki-arrow-left fs-4 me-1"></i>
                                Regresar
                            </a>

                            <button wire:click="exportarPDF" class="btn btn-sm btn-danger" wire:loading.attr="disabled" wire:target="exportarPDF">
                                <span wire:loading.remove wire:target="exportarPDF">
                                    <i class="ki-outline ki-file-down fs-4 me-1"></i>
                                    Exportar PDF
                                </span>
                                <span wire:loading wire:target="exportarPDF">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Generando PDF...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4 mb-7">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Tipo de Reporte</label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" wire:model.live="tipoReporte" value="recibidos" id="radioRecibidos">
                                    <label class="form-check-label" for="radioRecibidos">
                                        Documentos Recibidos
                                    </label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" wire:model.live="tipoReporte" value="enviados" id="radioEnviados">
                                    <label class="form-check-label" for="radioEnviados">
                                        Documentos Enviados
                                    </label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" wire:model.live="tipoReporte" value="todos" id="radioTodos">
                                    <label class="form-check-label" for="radioTodos">
                                        Todos los Documentos
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha Inicio</label>
                            <input type="date" wire:model.live="fechaInicio" class="form-control form-control-solid">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha Fin</label>
                            <input type="date" wire:model.live="fechaFin" class="form-control form-control-solid">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Estado</label>
                            <select wire:model.live="idEstado" class="form-select form-select-solid">
                                <option value="">Todos los estados</option>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id_estado }}">{{ $estado->nombre_estado }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Área</label>
                            <select wire:model.live="idArea" class="form-select form-select-solid">
                                <option value="">Todas las áreas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Buscar</label>
                            <div class="input-group">
                                <input type="text" wire:model.live.debounce.500ms="buscar"
                                       class="form-control form-control-solid"
                                       placeholder="Buscar por número, asunto o remitente...">
                                <button wire:click="limpiarFiltros" class="btn btn-light-primary" title="Limpiar todos los filtros">
                                    <i class="ki-outline ki-cross fs-4"></i>
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-7">
                        <div class="col-md-4">
                            <div class="card bg-light-primary border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px me-4">
                                            <span class="symbol-label bg-primary">
                                                <i class="ki-outline ki-document text-white fs-2x"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-muted fw-semibold d-block fs-7">Total Documentos</span>
                                            <span class="text-gray-800 fw-bold fs-2x">{{ $totalDocumentos }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-warning border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px me-4">
                                            <span class="symbol-label bg-warning">
                                                <i class="ki-outline ki-time text-white fs-2x"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-muted fw-semibold d-block fs-7">Pendientes</span>
                                            <span class="text-gray-800 fw-bold fs-2x">{{ $documentosPendientes }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-success border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px me-4">
                                            <span class="symbol-label bg-success">
                                                <i class="ki-outline ki-check text-white fs-2x"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-muted fw-semibold d-block fs-7">Atendidos</span>
                                            <span class="text-gray-800 fw-bold fs-2x">{{ $documentosAtendidos }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-200px rounded-start">Documento / Expediente</th>
                                    <th class="min-w-130px">Fechas</th>
                                    <th class="min-w-300px">Asunto & Detalles</th>
                                    <th class="min-w-220px">Flujo (Origen → Destino)</th>
                                    <th class="min-w-130px">Estado / Tiempo</th>
                                    <th class="min-w-80px text-end pe-4 rounded-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documentos as $documento)
                                    <tr>
                                        <!-- Documento / Expediente -->
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label bg-light-primary">
                                                        <i class="ki-outline ki-document text-primary fs-2"></i>
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <a href="{{ route('reportes.documentos.detalles', $documento->id_documento) }}"
                                                       class="text-gray-800 fw-bold fs-6 mb-1 text-hover-primary text-decoration-none"
                                                       title="Ver detalle">
                                                        {{ $documento->numero_documento }}
                                                    </a>
                                                    <span class="badge badge-light-secondary fs-8 fw-bold text-muted">
                                                        EXP: {{ $documento->expediente_documento ?? 'S/N' }}
                                                    </span>
                                                    <span class="text-muted fs-8 fw-semibold mt-1">
                                                        {{ $documento->tipoDocumento->descripcion_catalogo ?? 'Documento' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Fechas -->
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="mb-1" title="Fecha de Creación">
                                                    <span class="badge badge-light fs-8 fw-bold text-gray-600">
                                                        <i class="ki-outline ki-calendar me-1"></i> {{ Carbon\Carbon::parse($documento->au_fechacr)->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                                <div title="Hora">
                                                    <span class="text-gray-500 fs-8 fw-semibold ms-1">
                                                        {{ Carbon\Carbon::parse($documento->au_fechacr)->format('H:i:s') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Asunto & Detalles -->
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-medium fs-6 mb-2 text-break" style="max-width: 350px;">
                                                    {{ Str::limit($documento->asunto, 80) }}
                                                </span>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    @if($documento->folio_documento)
                                                    <span class="badge badge-outline badge-secondary fs-9" title="Número de Folios">
                                                        <i class="ki-outline ki-book-open fs-9 me-1"></i> {{ $documento->folio_documento }} Folios
                                                    </span>
                                                    @endif
                                                    @if($documento->archivos_count > 0)
                                                    <span class="badge badge-outline badge-primary fs-9" title="Archivos Adjuntos">
                                                        <i class="ki-outline ki-paperclip fs-9 me-1"></i> {{ $documento->archivos_count }} Adjuntos
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="mt-1">
                                                    <span class="text-gray-500 fs-8">Remitente: <span class="text-gray-700 fw-semibold">{{ $documento->oficina->descripcion_catalogo ?? 'N/A' }}</span></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Flujo Visual -->
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="bullet bullet-dot bg-danger me-2 h-10px w-10px"></div>
                                                    <div class="fs-8">
                                                        <span class="text-muted fw-semibold">De:</span>
                                                        <span class="text-gray-800 fw-bold">{{ Str::limit($documento->areaRemitente->nombre_area ?? 'Externo', 30) }}</span>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center ms-2 my-1">
                                                    <div class="h-15px border-start border-gray-300 border-dashed"></div>
                                                </div>

                                                <div class="d-flex align-items-center mt-1">
                                                    <div class="bullet bullet-dot bg-success me-2 h-10px w-10px"></div>
                                                    <div class="fs-8">
                                                        <span class="text-muted fw-semibold">Para:</span>
                                                        <span class="text-gray-800 fw-bold">{{ Str::limit($documento->areaDestino->nombre_area ?? 'Sin asignar', 30) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Estado / Tiempo -->
                                        <td>
                                            @php
                                                $estado = strtoupper($documento->estado->nombre_estado ?? 'N/A');
                                                $color = match(true) {
                                                    str_contains($estado, 'RECEPCIONADO') || str_contains($estado, 'FINALIZADO') => 'success',
                                                    str_contains($estado, 'PENDIENTE') => 'warning',
                                                    str_contains($estado, 'OBSERVADO') => 'danger',
                                                    str_contains($estado, 'ANULADO') => 'danger',
                                                    str_contains($estado, 'ARCHIVADO') => 'secondary',
                                                    default => 'primary'
                                                };
                                                $tiempoTranscurrido = Carbon\Carbon::parse($documento->au_fechacr)->diffForHumans();
                                            @endphp
                                            <div class="d-flex flex-column align-items-start">
                                                <span class="badge badge-light-{{ $color }} fw-bold fs-7 mb-1">
                                                    {{ $estado }}
                                                </span>
                                                <span class="text-muted fs-8 fw-semibold" title="Tiempo desde creación">
                                                    <i class="ki-outline ki-time fs-8 me-1"></i> {{ $tiempoTranscurrido }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Acciones -->
                                        <td class="text-end pe-4">
                                            <a href="{{ route('reportes.documentos.detalles', $documento->id_documento) }}"
                                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                               title="Ver historial completo">
                                                <i class="ki-outline ki-eye fs-3"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="symbol symbol-70px mb-3">
                                                    <div class="symbol-label bg-light-primary">
                                                        <i class="ki-outline ki-magnifier fs-2x text-primary"></i>
                                                    </div>
                                                </div>
                                                <span class="text-gray-800 fw-bold fs-4">No se encontraron resultados</span>
                                                <span class="text-muted fs-6">Intenta ajustar los filtros de búsqueda</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <div class="text-muted">
                            Mostrando {{ $documentos->firstItem() ?? 0 }} - {{ $documentos->lastItem() ?? 0 }} de {{ $documentos->total() }} documentos
                        </div>
                        <div>
                            {{ $documentos->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
