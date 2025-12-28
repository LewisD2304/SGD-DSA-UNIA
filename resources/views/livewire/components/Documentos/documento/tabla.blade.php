    <div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="d-flex flex-wrap flex-stack my-5 mx-8">
                    <div class="d-flex align-items-center position-relative my-1 me-4 fs-7">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" data-kt-user-table-filter="buscar" class="form-control form-control-solid ps-13 w-xl-350px w-300" placeholder="Buscar documento" wire:model.live.debounce.500ms="buscar" />
                    </div>

                    @can('autorizacion',['REGISTRAR','DOCUMENTOS'])
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary fw-bold mx-2" x-data @click="$dispatch('abrirModalDocumento')">
                            <i class="ki-outline ki-plus-circle fs-2"></i>
                            Nuevo
                        </button>
                    </div>
                    @endcan
                </div>

                <div class="card-body py-4">
                    <div class="dataTables_wrapper dt-bootstrap4 no-footer">

                        <div class="table-responsive" style="overflow: visible;">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                                <thead>
                                    <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                        <th class="w-10px pe-2">N°</th>
                                        <th class="min-w-125px">EXPEDIENTE</th>
                                        <th class="min-w-250px">ASUNTO</th>
                                        <th class="min-w-150px">REMITENTE</th>
                                        <th class="min-w-150px">DESTINO</th>
                                        <th class="min-w-125px">FECHA DE CREACION</th>
                                        <th class="min-w-100px">ESTADO</th>
                                        <th class="text-center min-w-100px">ACCIONES</th>
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
                                        <td>{{ formatoFechaText($documento->au_fechacr)}}</td>
                                        <td>
                                            @php
                                            // Obtener el área del usuario actual
                                            $areaUsuario = Auth::user()->persona->id_area ?? null;
                                            // Obtener el estado visual según el área
                                            $estadoVisual = $documento->getEstadoVisual($areaUsuario);

                                            if ($estadoVisual) {
                                            $nombreEstado = strtoupper($estadoVisual->nombre_estado);
                                            $colorEstado = match($nombreEstado) {
                                            'RECEPCIONADO' => 'success',
                                            'OBSERVADO' => 'danger',
                                            'DERIVADO' => 'secondary',
                                            'ARCHIVADO' => 'primary',
                                            'EN TRÁMITE', 'EN TRAMITE' => 'info',
                                            default => 'info'
                                            };
                                            }
                                            @endphp

                                            @if(isset($estadoVisual))
                                            <span class="badge badge-light-{{ $colorEstado }} py-2 px-3">
                                                {{ $estadoVisual->nombre_estado }}
                                            </span>
                                            @else
                                            <span class="badge badge-light-secondary py-2 px-3">Sin estado</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button type="button" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm fs-6" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                    Acciones
                                                    <i class="ki-outline ki-down fs-5 ms-1"></i>
                                                </button>

                                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-6 py-4 w-175px" data-kt-menu="true">

                                                    @php
                                                    // Verificar si el documento está archivado
                                                    $estaArchivado = $documento->id_estado == 6; // 6 = ARCHIVADO
                                                    @endphp

                                                    @can('autorizacion', ['VER', 'DOCUMENTOS'])
                                                    <div class="menu-item px-3">
                                                        <a href="#" class="menu-link px-3" wire:click="$dispatch('abrirModalDetalleDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                            <span class="menu-icon"><i class="ki-outline ki-eye fs-3"></i></span> Ver
                                                        </a>
                                                    </div>
                                                    @endcan

                                                    @if(!$estaArchivado)
                                                    {{-- ACCIONES PARA DOCUMENTOS ACTIVOS (NO ARCHIVADOS) --}}

                                                    @can('autorizacion', ['MODIFICAR', 'DOCUMENTOS'])
                                                    <div class="menu-item px-3">
                                                        <a href="#" class="menu-link px-3" wire:click="$dispatch('abrirModalDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                            <span class="menu-icon"><i class="ki-outline ki-pencil fs-3"></i></span> Modificar
                                                        </a>
                                                    </div>
                                                    @endcan

                                                    @can('autorizacion', ['ELIMINAR', 'DOCUMENTOS'])
                                                    <div class="menu-item px-3">
                                                        <a href="#" class="menu-link px-3 text-danger" wire:click="$dispatch('abrirModalEliminarDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                            <span class="menu-icon"><i class="ki-outline ki-trash fs-3 text-danger"></i></span> Eliminar
                                                        </a>
                                                    </div>
                                                    @endcan

                                                    @if(Auth::user()->can('autorizacion', ['DERIVAR', 'DOCUMENTOS']))
                                                    <div class="separator my-2"></div>
                                                    @endif

                                                    @can('autorizacion', ['DERIVAR', 'DOCUMENTOS'])
                                                    @php
                                                    $areaUsuario = Auth::user()->persona->id_area ?? null;
                                                    $puedeDerivar = $areaUsuario &&
                                                    $documento->id_area_destino == $areaUsuario &&
                                                    $documento->fecha_recepcion_documento !== null;
                                                    @endphp
                                                    @if($puedeDerivar)
                                                    <div class="menu-item px-3">
                                                        <a href="#" class="menu-link px-3" wire:click="$dispatch('abrirModalDerivarDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                            <span class="menu-icon"><i class="ki-outline ki-arrow-right fs-3"></i></span> Derivar
                                                        </a>
                                                    </div>
                                                    @endif
                                                    @endcan
                                                    @endif

                                                    {{-- ACCIONES EXCLUSIVAS PARA DOCUMENTOS ARCHIVADOS --}}
                                                    @if($estaArchivado)
                                                    <div class="separator my-2"></div>

                                                    @can('autorizacion', ['RECTIFICAR', 'DOCUMENTOS'])
                                                    <div class="menu-item px-3">
                                                        <a href="#" class="menu-link px-3 text-warning" wire:click="$dispatch('abrirModalRectificarDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                            <span class="menu-icon">
                                                                <i class="ki-outline ki-pencil fs-4 text-warning"></i>
                                                            </span>
                                                            Rectificar
                                                        </a>
                                                    </div>
                                                    @endcan

                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-8 text-muted">
                                            <div x-data="{ cargado: false }" x-init="cargado = true">
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

                            <div class="mt-4">
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
