<div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
    <div class="col-12">
        <div class="card">
            <div class="d-flex flex-wrap flex-stack my-3 my-md-5 mx-3 mx-md-8">
                <div class="d-flex align-items-center position-relative my-2 me-2 me-md-4 fs-7 w-100 w-md-auto">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" data-kt-user-table-filter="buscar" class="form-control form-control-solid ps-13 w-100" placeholder="Buscar área" wire:model.live.debounce.500ms="buscar" style="max-width: 350px;" />
                </div>

                <div class="d-flex my-2 w-100 w-md-auto">
                    @can('autorizacion', ['REGISTRAR', 'AREA'])
                        <button type="button" class="btn btn-primary px-3 px-sm-6 w-100 w-md-auto" x-data @click="$dispatch('abrirModalArea')">
                            <template x-if="!cargando">
                                <i class="ki-outline ki-plus fs-2 px-0"></i>
                            </template>
                            <template x-if="cargando">
                                <span>
                                    <x-spinner style="width: 20px; height: 20px;" />
                                </span>
                            </template>
                            <span class="d-none d-sm-inline">
                                Nuevo
                            </span>
                            <span class="d-inline d-sm-none">
                                Nuevo Área
                            </span>
                        </button>
                    @endcan
                </div>
            </div>

            <div class="card-body py-4">
                <div lass="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                            <thead>
                                <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                    <th class="w-10px pe-2">N°</th>
                                    <th class="min-w-200px">NOMBRE DEL ÁREA</th>
                                    <th class="min-w-100px d-none d-md-table-cell">SIGLAS</th>
                                    <th class="min-w-220px d-none d-lg-table-cell">PERSONAS ASIGNADAS</th>
                                    <th class="min-w-150px d-none d-xl-table-cell">FECHA DE CREACIÓN</th>
                                    <th class="min-w-60px">ESTADO</th>
                                    <th class="text-center min-w-60px">ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600" wire:loading.class="opacity-25" wire:target="buscar, gotoPage, previousPage, nextPage">
                                @php
                                $contador = $this->areas->firstItem();
                                @endphp
                                @forelse ($this->areas as $item)
                                <tr wire:key="{{ $item->id_area }}">
                                    <td>{{ $contador++ }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->nombre_area }}</div>
                                        <div class="d-md-none">
                                            <span class="badge badge-light-info mt-1">{{ $item->siglas_area }}</span>
                                        </div>
                                    </td>
                                    <!-- SIGLAS -->
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge badge-light-info">{{ $item->siglas_area }}</span>
                                    </td>
                                    <!-- PERSONAS ASIGNADAS -->
                                    <td class="d-none d-lg-table-cell">
                                        @php
                                            $personas = $item->personas ?? collect();
                                        @endphp

                                        @if ($personas->isEmpty())
                                            <span class="text-muted">Sin personas</span>
                                        @else
                                            <div class="d-flex flex-column gap-1">
                                                @foreach ($personas as $persona)
                                                    <span class="badge badge-light-primary fw-semibold px-3 py-2 text-start">
                                                        {{ trim($persona->nombres_persona . ' ' . $persona->apellido_paterno_persona . ' ' . $persona->apellido_materno_persona) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <!-- FECHA DE CREACIÓN -->
                                    <td class="d-none d-xl-table-cell">{{ formatoFechaText($item->au_fechacr) }}</td>
                                    <!-- ESTADO -->
                                    <td>
                                        @if ($item->estado_area === \App\Enums\EstadoEnum::HABILITADO)
                                        <span class="badge py-2 px-4 fs-6 fw-normal badge-light-success cursor-default">
                                            <span class="bottom-0 bg-success rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                            {{ \App\Enums\EstadoEnum::HABILITADO->descripcion() }}
                                        </span>
                                        @elseif ($item->estado_area === \App\Enums\EstadoEnum::DESHABILITADO)
                                        <span class="badge py-2 px-4 fs-6 fw-normal badge-light-danger cursor-default">
                                            <span class="bottom-0 bg-danger rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                            {{ \App\Enums\EstadoEnum::DESHABILITADO->descripcion() }}
                                        </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <a class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm fs-6" data-bs-toggle="dropdown">
                                            Acciones
                                            <i class="ki-outline ki-down fs-5 ms-1"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-6 py-4" style="width: 180px;" data-kt-menu="true" x-data="{ cargando_opciones: false }" onclick="event.stopPropagation();">
                                            @if(!empty($permisos['MODIFICAR']))
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalArea', { id_area: {{ $item->id_area }} });" :disabled="cargando" :class="{ 'active': cargando }" onclick="event.stopPropagation();">
                                                        <span>Modificar</span>
                                                        <template x-if="cargando">
                                                            <span>
                                                                <x-spinner class="ms-2" style="width: 20px; height: 20px;" />
                                                            </span>
                                                        </template>
                                                    </a>
                                                </div>
                                            @endif
                                            @if(!empty($permisos['CAMBIAR_ESTADO']))
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEstado', { id_area: {{ $item->id_area }} });" :disabled="cargando" :class="{ 'active': cargando }" onclick="event.stopPropagation();">
                                                        Estado
                                                        <template x-if="cargando">
                                                            <span>
                                                                <x-spinner class="ms-2" style="width: 20px; height: 20px;" />
                                                            </span>
                                                        </template>
                                                    </a>
                                                </div>
                                            @endif
                                            @if(!empty($permisos['ELIMINAR']))
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3 menu-link-danger text-danger" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEliminar', { id_area: {{ $item->id_area }} });" :disabled="cargando" :class="{ 'active-danger': cargando }" onclick="event.stopPropagation();">
                                                        Eliminar
                                                        <template x-if="cargando">
                                                            <span>
                                                                <x-spinner class="ms-2" style="width: 20px; height: 20px;" />
                                                            </span>
                                                        </template>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-muted">
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

                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                            <div class="d-flex align-items-center text-muted fw-semibold">
                                Mostrando {{ $this->areas->firstItem() }} - {{ $this->areas->lastItem() }} de {{ $this->areas->total() }} registros
                            </div>

                            @if ($this->areas->hasPages())
                                <nav aria-label="...">
                                    <ul class="pagination mb-0">
                                        <!-- Previous Page Link -->
                                        @if ($this->areas->onFirstPage())
                                            <li class="page-item disabled"><span class="page-link">9 Anterior</span></li>
                                        @else
                                            <li class="page-item">
                                                <button type="button" wire:click="previousPage" class="page-link">9 Anterior</button>
                                            </li>
                                        @endif
                                        @foreach ($this->areas->getUrlRange(1, $this->areas->lastPage()) as $page => $url)
                                            @if ($page == $this->areas->currentPage())
                                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                            @else
                                                <li class="page-item">
                                                    <button type="button" wire:click="gotoPage({{ $page }})" class="page-link">{{ $page }}</button>
                                                </li>
                                            @endif
                                        @endforeach
                                        <!-- Next Page Link -->
                                        @if ($this->areas->hasMorePages())
                                            <li class="page-item">
                                                <button type="button" wire:click="nextPage" class="page-link">Siguiente 1</button>
                                            </li>
                                        @else
                                            <li class="page-item disabled"><span class="page-link">Siguiente 1</span></li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
