<div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
    <div class="col-12">
        <div class="card">
            <div class="d-flex flex-wrap flex-stack my-5 mx-8">
                <div class="d-flex flex-column">
                    <div class="d-flex align-items-center position-relative my-1 me-4 fs-7">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" class="form-control form-control-solid ps-13 w-xl-350px w-300"
                               placeholder="Buscar documento" wire:model.live.debounce.500ms="buscar" />
                    </div>
                    <div class="text-muted fs-7 mt-2">
                        <i class="ki-outline ki-information-5 fs-5"></i>
                        Aquí se muestran todos los documentos que tu área ha derivado a otras áreas
                    </div>
                </div>
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
                                    <th class="min-w-150px">REMITENTE</th>
                                    <th class="min-w-150px">DESTINO</th>
                                    <th class="min-w-125px">FECHA RECEPCION</th>
                                    <th class="min-w-125px">FECHA EMISION</th>
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
                                @endphp
                                <tr wire:key="historial-mov-{{ $movimiento->id_movimiento }}">
                                    <td>{{ $contador++ }}</td>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $documento->expediente_documento }}</div>
                                        @if($documento->numero_documento)
                                        <div class="text-muted fs-7">{{ $documento->numero_documento }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-gray-800">{{ Str::limit($documento->asunto_documento, 100) }}</div>
                                    </td>
                                    <td>
                                        <div class="text-gray-800">{{ $documento->areaRemitente->nombre_area ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-gray-800">{{ $documento->areaDestino->nombre_area ?? 'N/A' }}</div>
                                    </td>
                                    @php
                                        $nombreEstadoMov = strtoupper($movimiento->estado->nombre_estado ?? '');
                                        $esRecepcion = in_array($nombreEstadoMov, ['EN TRÁMITE', 'EN TRAMITE', 'RECEPCIONADO']);
                                        $esDerivar = $nombreEstadoMov === 'DERIVADO';
                                    @endphp
                                    <td>{{ $esRecepcion ? formatoFechaText($movimiento->fecha_recepcion ?? $movimiento->au_fechacr) : '' }}</td>
                                    <td>{{ $esDerivar ? formatoFechaText($movimiento->au_fechacr) : '' }}</td>
                                    <td>
                                        @php
                                        $estadoVis = $movimiento->estado ?: $documento->estado;
                                        @endphp
                                        @if($estadoVis)
                                        @php
                                        $nombreEstado = strtoupper($estadoVis->nombre_estado);
                                        $colorEstado = match($nombreEstado) {
                                            'RECEPCIONADO' => 'success',
                                            'OBSERVADO' => 'danger',
                                            'DERIVADO' => 'secondary',
                                            'ARCHIVADO' => 'primary',
                                            default => 'info'
                                        };
                                        @endphp
                                        <span class="badge badge-light-{{ $colorEstado }} py-2 px-3">
                                            {{ $estadoVis->nombre_estado }}
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
                                            title="Ver detalles"
                                        >
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-muted">
                                        <div x-data="{ cargado: false }" x-init="cargado = true">
                                            <template x-if="cargado">
                                                <x-blank-state-table mensaje="No se encontraron documentos en el historial" />
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
