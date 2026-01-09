<div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
    <div class="col-12">
        <div class="card">
            <div class="d-flex flex-wrap flex-stack my-5 mx-8 gap-3">
                <!-- BÚSQUEDA -->
                <div class="d-flex align-items-center position-relative my-1 fs-7">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" class="form-control form-control-solid ps-13 w-xl-300px w-250" placeholder="Buscar documento" wire:model.live.debounce.500ms="buscar" />
                </div>

                <!-- FILTRO DE FECHAS -->
                <div class="d-flex gap-2 my-1">
                    <input type="date" class="form-control form-control-solid" placeholder="Fecha inicio" wire:model.live="fechaInicio" max="{{ $fechaFin }}" title="Fecha de inicio" style="max-width: 150px;" />
                    <input type="date" class="form-control form-control-solid" placeholder="Fecha fin" wire:model.live="fechaFin" min="{{ $fechaInicio }}" title="Fecha fin" style="max-width: 150px;" />
                </div>

                <!-- FILTRO DE ESTADO -->
                <select class="form-select form-select-solid my-1" wire:model.live="idEstadoFiltro" style="max-width: 200px;">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $idEstado => $nombreEstado)
                    <option value="{{ $idEstado }}">{{ $nombreEstado }}</option>
                    @endforeach
                </select>

                <!-- BOTÓN LIMPIAR FILTROS -->
                <button type="button" class="btn btn-light-secondary fw-bold my-1" wire:click="limpiarFiltros">
                    <i class="ki-outline ki-trash fs-2"></i>
                    Limpiar
                </button>
            </div>

            <div class="card-body py-4">
                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                            <thead>
                                <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                    <th class="w-10px pe-2">N°</th>
                                    <th class="min-w-125px">EXPEDIENTE</th>
                                    <th class="min-w-250px">ASUNTO</th>
                                    <th class="min-w-120px">ACCIÓN / MOVIMIENTO</th>
                                    <th class="min-w-120px">ÁREA ORIGEN</th>
                                    <th class="min-w-120px">ÁREA DESTINO</th>
                                    <th class="min-w-140px">FECHA DEL MOVIMIENTO</th>
                                    <th class="min-w-100px">ESTADO</th>
                                    <th class="text-center min-w-70px">VER</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600" wire:loading.class="opacity-25" wire:target="buscar">
                                @php
                                $contador = $this->historial->firstItem();
                                @endphp
                                @forelse ($this->historial as $movimiento)
                                @php
                                    $documento = $movimiento->documento;
                                    $estadoMovimiento = $movimiento->estado?->nombre_estado ?? 'SIN ESTADO';
                                    $areaOrigen = $movimiento->areaOrigen?->nombre_area ?? $documento?->areaRemitente?->nombre_area ?? 'N/A';
                                    $areaDestino = $movimiento->areaDestino?->nombre_area ?? $documento?->areaDestino?->nombre_area ?? 'N/A';
                                    $fechaMovimiento = $movimiento->au_fechacr;
                                @endphp
                                <tr wire:key="historial-mov-{{ $movimiento->id_movimiento }}">
                                    <td>{{ $contador++ }}</td>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $documento->expediente_documento }}</div>
                                        @if($documento->numero_documento)
                                        <div class="text-muted fs-7">{{ Str::limit($documento->numero_documento, 20) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-gray-800 fw-semibold">{{ Str::limit($documento->asunto_documento, 80) }}</div>
                                    </td>
                                    <td>
                                        @php
                                            // Determinar el tipo de acción
                                            $tipoAccion = strtoupper($estadoMovimiento);
                                            $badgeClass = match($tipoAccion) {
                                                'RECEPCIONADO', 'FINALIZADO', 'SUBSANADO' => 'badge-light-success',
                                                'DERIVADO' => 'badge-light-primary',
                                                'OBSERVADO' => 'badge-light-danger',
                                                'ARCHIVADO' => 'badge-light-info',
                                                'POR RECTIFICAR', 'SOLICITAR RECTIFICACION' => 'badge-light-warning',
                                                default => 'badge-light-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} fw-bold">
                                            {{ $tipoAccion }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-gray-800 fw-semibold">{{ $areaOrigen }}</div>
                                    </td>
                                    <td>
                                        <div class="text-gray-800 fw-semibold">{{ $areaDestino }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-gray-800">
                                            <i class="bi bi-calendar-event me-2 text-primary"></i>
                                            {{ \Carbon\Carbon::parse($fechaMovimiento)->format('d/m/Y') }}
                                        </div>
                                        <div class="text-muted fs-7">
                                            <i class="bi bi-clock me-2"></i>
                                            {{ \Carbon\Carbon::parse($fechaMovimiento)->format('H:i A') }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                        $estadoDoc = $documento->estado;
                                        @endphp
                                        @if($estadoDoc)
                                        @php
                                        $nombreEstado = strtoupper($estadoDoc->nombre_estado);
                                        $colorEstado = match($nombreEstado) {
                                            'RECEPCIONADO' => 'success',
                                            'OBSERVADO' => 'danger',
                                            'DERIVADO' => 'secondary',
                                            'ARCHIVADO' => 'primary',
                                            'FINALIZADO' => 'info',
                                            default => 'warning'
                                        };
                                        @endphp
                                        <span class="badge badge-light-{{ $colorEstado }} fw-bold py-2 px-3">
                                            {{ Str::limit($nombreEstado, 15) }}
                                        </span>
                                        @else
                                        <span class="badge badge-light-secondary py-2 px-3">Sin estado</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-icon btn-light-primary btn-sm"
                                            wire:click="$dispatch('abrirModalDetalleDocumento', { id_documento: {{ $documento->id_documento }} })"
                                            title="Ver detalles completos"
                                        >
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-8 text-muted">
                                        <div x-data="{ cargado: false }" x-init="cargado = true">
                                            <template x-if="cargado">
                                                <x-blank-state-table mensaje="No se encontraron movimientos en el historial" />
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="position-absolute top-50 start-50 translate-middle" style="margin-top: 1.06rem;"
                             wire:loading wire:target="buscar">
                            <x-spinner class="text-primary" style="width: 35px; height: 35px;" />
                        </div>

                        <div>
                            @if ($this->historial->hasPages())
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center">
                                    Mostrando {{ $this->historial->firstItem() }} - {{ $this->historial->lastItem() }}
                                    de {{ $this->historial->total() }} registros
                                </div>
                                <div class="pagination pagination-lg">{{ $this->historial->links() }}</div>
                            </div>
                            @else
                            <div class="d-flex justify-content-between py-2">
                                <div class="d-flex align-items-center text-muted">
                                    Mostrando {{ $this->historial->firstItem() }} - {{ $this->historial->lastItem() }}
                                    de {{ $this->historial->total() }} registros
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
