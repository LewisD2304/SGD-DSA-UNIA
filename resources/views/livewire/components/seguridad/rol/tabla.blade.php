<div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
    <div class="col-12">
        <div class="card">

            <div class="d-flex flex-wrap flex-stack my-5 mx-8">
                <div class="d-flex align-items-center position-relative my-1 me-4 fs-7">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" class="form-control form-control-solid ps-13 w-xl-350px w-300" placeholder="Buscar rol" wire:model.live.debounce.500ms="buscar" />
                </div>

                @can('autorizacion', ['REGISTRAR', 'ROLES'])
                <div class="d-flex my-2">
                    <button type="button" class="btn btn-primary px-4 px-sm-6" x-data @click="$dispatch('abrirModalRol')">
                        <template x-if="!cargando">
                            <i class="ki-outline ki-plus fs-2 px-0"></i>
                        </template>
                        <template x-if="cargando">
                            <span>
                                <x-spinner style="width: 20px; height: 20px;" /></span>
                        </template>
                        <span class="d-none d-sm-inline ms-2">Nuevo</span>
                    </button>
                </div>
                @endcan
            </div>

            <div class="card-body py-4">
                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                            <thead>
                                <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                    <th class="w-10px pe-2">N°</th>
                                    <th class="min-w-200px">NOMBRE ROL</th>
                                    <th class="min-w-60px">ESTADO</th>

                                    <th class="text-center min-w-100px">CONF. ACCESO</th>

                                    <th class="text-center min-w-100px">ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600" wire:loading.class="opacity-25" wire:target="buscar, gotoPage, previousPage, nextPage">
                                @php
                                $contador = $this->roles->firstItem();
                                @endphp
                                @forelse ($this->roles as $item)
                                <tr wire:key="{{ $item->id_rol }}">
                                    <td>{{ $contador++ }}</td>
                                    <td>{{ $item->nombre_rol }}</td>
                                    <td>
                                        @if ($item->estado_rol === \App\Enums\EstadoEnum::HABILITADO)
                                        <span class="badge py-2 px-4 fs-6 fw-normal badge-light-success cursor-default">
                                            <span class="bottom-0 bg-success rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                            {{ \App\Enums\EstadoEnum::HABILITADO->descripcion() }}
                                        </span>
                                        @elseif ($item->estado_rol === \App\Enums\EstadoEnum::DESHABILITADO)
                                        <span class="badge py-2 px-4 fs-6 fw-normal badge-light-danger cursor-default">
                                            <span class="bottom-0 bg-danger rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                            {{ \App\Enums\EstadoEnum::DESHABILITADO->descripcion() }}
                                        </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if(!empty($permisos['CONFIGURAR']))
                                        <div x-data="{ cargando: false }">
                                            <button type="button" class="btn btn-icon btn-light-primary btn-active-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Configurar permisos y accesos" @click="cargando = true" wire:click="configurarAcceso({{ $item->id_rol }})" :disabled="cargando">
                                                <template x-if="!cargando">
                                                    <i class="ki-outline ki-setting-2 fs-2"></i>
                                                </template>

                                                <template x-if="cargando">
                                                    <span>
                                                        <x-spinner style="width: 20px; height: 20px;" /></span>
                                                </template>
                                            </button>
                                        </div>
                                        @else
                                        <span class="text-muted fs-8">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div>
                                            <a class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm fs-6" data-bs-toggle="dropdown">
                                                Acciones
                                                <i class="ki-outline ki-down fs-5 ms-1"></i>
                                            </a>

                                            <div class="dropdown-menu dropdown-menu-end menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-6 py-4" style="width: 180px;" data-kt-menu="true" x-data="{ cargando_opciones: false }" onclick="event.stopPropagation();">

                                                @if(!empty($permisos['MODIFICAR']))
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalRol', { id_rol: {{ $item->id_rol }} });" :disabled="cargando" :class="{ 'active': cargando }" onclick="event.stopPropagation();">
                                                        <span>Modificar</span>
                                                        <template x-if="cargando">
                                                            <span>
                                                                <x-spinner class="ms-2" style="width: 20px; height: 20px;" /></span>
                                                        </template>
                                                    </a>
                                                </div>
                                                @endif

                                                @if(!empty($permisos['CAMBIAR_ESTADO']))
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEstado', { id_rol: {{ $item->id_rol }} });" :disabled="cargando" :class="{ 'active': cargando }" onclick="event.stopPropagation();">
                                                        <span>Estado</span>
                                                        <template x-if="cargando">
                                                            <span>
                                                                <x-spinner class="ms-2" style="width: 20px; height: 20px;" /></span>
                                                        </template>
                                                    </a>
                                                </div>
                                                @endif

                                                @if(!empty($permisos['ELIMINAR']))
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3 menu-link-danger text-danger" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEliminar', { id_rol: {{ $item->id_rol }} });" :disabled="cargando" :class="{ 'active-danger': cargando }" onclick="event.stopPropagation();">
                                                        Eliminar
                                                        <template x-if="cargando">
                                                            <span>
                                                                <x-spinner class="ms-2" style="width: 20px; height: 20px;" /></span>
                                                        </template>
                                                    </a>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-muted">
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

                        <div>
                            @if ($this->roles->hasPages())
                            <div class="d-flex justify-content-between mt-4">
                                <div class="d-flex align-items-center text-gray-600">
                                    Mostrando {{ $this->roles->firstItem() }} - {{ $this->roles->lastItem() }} de {{ $this->roles->total() }} registros
                                </div>
                                <div class="pagination pagination-lg">{{ $this->roles->links() }}</div>
                            </div>
                            @else
                            <div class="d-flex justify-content-between py-2 mt-4">
                                <div class="d-flex align-items-center text-muted">
                                    Mostrando {{ $this->roles->firstItem() }} - {{ $this->roles->lastItem() }} de {{ $this->roles->total() }} registros
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
