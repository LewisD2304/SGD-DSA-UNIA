    <div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="d-flex flex-wrap flex-stack my-5 mx-8">
                    <div class="d-flex align-items-center position-relative my-1 me-4 fs-7">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" data-kt-user-table-filter="buscar" class="form-control form-control-solid ps-13 w-xl-350px w-300" placeholder="Buscar persona" wire:model.live.debounce.500ms="buscar" />
                    </div>

                    <div class="d-flex my-2">
                        <button type="button" class="btn btn-primary px-4 px-sm-6" x-data @click="$dispatch('abrirModalPersona')">
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
                        </button>
                    </div>
                </div>

                <div class="card-body py-4">
                    <div lass="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                                <thead>
                                    <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                        <th class="w-10px pe-2">N°</th>
                                        <th class="min-w-200px">NOMBRE PERSONA</th>
                                        <th class="min-w-200px">DOCUMENTO</th>
                                        <th class="min-w-60px">FECHA DE CREACION</th>
                                        <th class="min-w-60px">ESTADO</th>
                                        <th class="min-w-60px">DETALLES</th>
                                        <th class="text-center min-w-60px">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600" wire:loading.class="opacity-25" wire:target="buscar, gotoPage, previousPage, nextPage">
                                    @php
                                    $contador = $this->personas->firstItem();
                                    @endphp
                                    @forelse ($this->personas as $item)
                                    <tr wire:key="{{ $item->id_persona }}">
                                        <td>{{ $contador++ }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->nombres_persona}}, {{ $item->apellido_paterno_persona}} {{ $item->apellido_materno_persona}}
                                        </td>
                                        <!-- DOCUMENTO DE IDENTIDAD -->
                                        <td>{{ $item->numerodocumento_persona}}</td>
                                        <!-- FECHA DE CREACION -->
                                        <td>{{ formatoFechaText($item->au_fechacr)}}</td>
                                        <!-- ESTADO -->
                                        <td>
                                            @if ($item->estado_persona === \App\Enums\EstadoEnum::HABILITADO)
                                            <span class="badge py-2 px-4 fs-6 fw-normal badge-light-success cursor-default">
                                                <span class="bottom-0 bg-success rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                                {{ \App\Enums\EstadoEnum::HABILITADO->descripcion() }}
                                            </span>
                                            @elseif ($item->estado_persona === \App\Enums\EstadoEnum::DESHABILITADO)
                                            <span class="badge py-2 px-4 fs-6 fw-normal badge-light-danger cursor-default">
                                                <span class="bottom-0 bg-danger rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                                {{ \App\Enums\EstadoEnum::DESHABILITADO->descripcion() }}
                                            </span>
                                            @endif
                                        </td>

                                        <td class="align-middle text-center">
                                            <div class="d-flex justify-content-center"></div>
                                            <button type="button" class="btn btn-icon btn-light btn-sm" x-data @click="$dispatch('abrirModalDetalle', { id_persona: {{ $item->id_persona }} })" title="Ver detalles">
                                                <i class="ki-duotone ki-eye fs-3 text-primary">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </button>
                                        </td>

                                        <td class="text-center">
                                            <a class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm fs-6" data-bs-toggle="dropdown">
                                                Acciones
                                                <i class="ki-outline ki-down fs-5 ms-1"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-6 py-4" style="width: 180px;" data-kt-menu="true" x-data="{ cargando_opciones: false }" onclick="event.stopPropagation();">
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalPersona', { id_persona: {{ $item->id_persona }} });" :disabled="cargando" :class="{ 'active': cargando }" onclick="event.stopPropagation();">
                                                        <span>Modificar</span>
                                                        <template x-if="cargando">
                                                            <span>
                                                                <x-spinner class="ms-2" style="width: 20px; height: 20px;" />
                                                            </span>
                                                        </template>
                                                    </a>
                                                </div>
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEstado', { id_persona: {{ $item->id_persona }} });" :disabled="cargando" :class="{ 'active': cargando }" onclick="event.stopPropagation();">
                                                        Estado
                                                        <template x-if="cargando">
                                                            <span>
                                                                <x-spinner class="ms-2" style="width: 20px; height: 20px;" />
                                                            </span>
                                                        </template>
                                                    </a>
                                                </div>
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3 menu-link-danger text-danger" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEliminar', { id_persona: {{ $item->id_persona }} });" :disabled="cargando" :class="{ 'active-danger': cargando }" onclick="event.stopPropagation();">
                                                        Eliminar
                                                        <template x-if="cargando">
                                                            <span>
                                                                <x-spinner class="ms-2" style="width: 20px; height: 20px;" />
                                                            </span>
                                                        </template>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-8 text-muted">
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
                                @if ($this->personas->hasPages())
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex align-items-center">Mostrando {{ $this->personas->firstItem() }} - {{ $this->personas->lastItem() }} de {{ $this->personas->total() }} registros</div>
                                    <div class="pagination pagination-lg">{{ $this->personas->links() }}</div>
                                </div>
                                @else
                                <div class="d-flex justify-content-between py-2">
                                    <div class="d-flex align-items-center text-muted
                                        ">Mostrando {{ $this->personas->firstItem() }} - {{ $this->personas->lastItem() }} de {{ $this->personas->total() }} registros</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
