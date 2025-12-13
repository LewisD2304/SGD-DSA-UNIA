    <div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="d-flex flex-wrap flex-stack my-5 mx-8">
                    <div class="d-flex align-items-center position-relative my-1 me-4 fs-7">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" data-kt-user-table-filter="buscar" class="form-control form-control-solid ps-13 w-xl-350px w-300" placeholder="Buscar documento" wire:model.live.debounce.500ms="buscar" />
                    </div>
                </div>

                <div class="card-body py-4">
                    <div lass="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                                <thead>
                                    <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                        <th class="w-10px pe-2">N°</th>
                                        <th class="min-w-125px">EXPEDIENTE</th>
                                        <th class="min-w-250px">ASUNTO</th>
                                        <th class="min-w-150px">REMITENTE</th>
                                        <th class="min-w-150px">DESTINO</th>
                                        <th class="min-w-125px">FECHA RECEPCIÓN</th>
                                        <th class="min-w-100px">ESTADO</th>
                                        <th class="text-center min-w-150px">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600" wire:loading.class="opacity-25" wire:target="buscar, gotoPage, previousPage, nextPage">
                                    @php
                                    $contador = $this->documentos->firstItem();
                                    @endphp
                                    @forelse ($this->documentos as $documento)
                                    <tr wire:key="documento-{{ $documento->id_documento }}">
                                        <td>{{ $contador++ }}</td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $documento->expediente_documento }}</div>
                                            @if($documento->numero_documento)
                                                <div class="text-muted fs-7">N°: {{ $documento->numero_documento }}</div>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="text-gray-800">{{ Str::limit($documento->asunto_documento, 60) }}</div>
                                        </td>

                                        <td>
                                            <div class="text-gray-800">{{ $documento->areaRemitente->nombre_area ?? 'N/A' }}</div>
                                        </td>

                                        <td>
                                            <div class="text-gray-800">{{ $documento->areaDestino->nombre_area ?? 'N/A' }}</div>
                                        </td>

                                        <td>
                                            {{ $documento->fecha_recepcion_documento ? \Carbon\Carbon::parse($documento->fecha_recepcion_documento)->format('d/m/Y') : '-' }}
                                        </td>

                                        <td>
                                            @if($documento->estado)
                                                <span class="badge badge-light-secondary py-2 px-3">
                                                    {{ $documento->estado->nombre_estado }}
                                                </span>
                                            @else
                                                <span class="badge badge-light-secondary py-2 px-3">Sin estado</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                @can('autorizacion',['VER','DOCUMENTOS'])
                                                <button
                                                    type="button"
                                                    class="btn btn-light btn-sm"
                                                    wire:click="$dispatch('abrirModalDetalleDocumento', { id_documento: {{ $documento->id_documento }} })"
                                                >
                                                    <i class="ki-outline ki-eye fs-4 me-1"></i>
                                                    Ver
                                                </button>
                                                @endcan

                                                <button
                                                    type="button"
                                                    class="btn btn-success btn-sm"
                                                    wire:click="recepcionar({{ $documento->id_documento }})"
                                                >
                                                    <i class="ki-outline ki-folder-check fs-4 me-1"></i>
                                                    Recepcionar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-8 text-muted">
                                            <!-- Mostrar mensaje si no hay registros -->
                                            <div x-data="{ cargado: false, modo: localStorage.getItem('data-bs-theme-mode') || 'light' }" x-init="cargado = true">
                                                <template x-if="cargado">
                                                    <x-blank-state-table mensaje="No se encontraron registros" />
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="position-absolute top-50 start-50 translate-middle" style="margin-top: 1.06rem;" wire:loading wire:target="buscar, gotoPage, previousPage, nextPage">
                                <x-spinner class="text-primary" style="width: 35px; height: 35px;" />
                            </div>

                            <div>
                                @if ($this->documentos->hasPages())
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex align-items-center">Mostrando {{ $this->documentos->firstItem() }} - {{ $this->documentos->lastItem() }} de {{ $this->documentos->total() }} registros</div>
                                    <div class="pagination pagination-lg">{{ $this->documentos->links() }}</div>
                                </div>
                                @else
                                <div class="d-flex justify-content-between py-2">
                                    <div class="d-flex align-items-center text-muted">Mostrando {{ $this->documentos->firstItem() }} - {{ $this->documentos->lastItem() }} de {{ $this->documentos->total() }} registros</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
