<div>
    <div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="d-flex flex-wrap flex-stack my-5 mx-8">
                    <div class="d-flex align-items-center position-relative my-1 me-4 fs-7">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" data-kt-user-table-filter="buscar" class="form-control form-control-solid ps-13 w-xl-350px w-300" placeholder="Buscar usuario" wire:model.live.debounce.500ms="buscar" />
                    </div>

                    <div class="d-flex my-2">
                        @can('autorizacion', ['REGISTRAR', 'USUARIOS'])
                        <button type="button" class="btn btn-primary px-4 px-sm-6" x-data="{ cargando: false }" @cargando.window="cargando = false" @click="cargando = true; $dispatch('abrirModalUsuario')">
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
                                        <th class="min-w-200px">NOMBRE USUARIO</th>
                                        <th class="min-w-250px">PERSONA</th>
                                        <th class="min-w-200px">ROL ASIGNADO</th>
                                        <th class="min-w-60px">ESTADO</th>
                                        <th class="min-w-60px">FECHA DE CREACION</th>
                                        <th class="text-center min-w-60px">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600" wire:loading.class="opacity-25" wire:target="buscar, gotoPage, previousPage, nextPage">
                                    @php
                                    $contador = $this->usuarios->firstItem();
                                    @endphp
                                    @forelse ($this->usuarios as $item)
                                    <tr wire:key="{{ $item->id_usuario }}">
                                        <td>{{ $contador++ }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->nombre_usuario }}
                                        </td>
                                        <!-- PERSONA -->
                                        <td>
                                            @if($item->persona)
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-semibold">{{ $item->persona->nombres_persona }} {{ $item->persona->apellido_paterno_persona }} {{ $item->persona->apellido_materno_persona }}</span>
                                                    <span class="text-muted fs-7">{{ $item->persona->numerodocumento_persona }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted">Sin persona asignada</span>
                                            @endif
                                        </td>
                                        <!-- ROL -->
                                        <td>{{ $item->rol ? $item->rol->nombre_rol : 'Sin rol' }}</td>
                                        <!-- ESTADO -->
                                        <td>
                                            @if ($item->estado_usuario === \App\Enums\EstadoEnum::HABILITADO)
                                            <span class="badge py-2 px-4 fs-6 fw-normal badge-light-success cursor-default">
                                                <span class="bottom-0 bg-success rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                                {{ \App\Enums\EstadoEnum::HABILITADO->descripcion() }}
                                            </span>
                                            @elseif ($item->estado_usuario === \App\Enums\EstadoEnum::DESHABILITADO)
                                            <span class="badge py-2 px-4 fs-6 fw-normal badge-light-danger cursor-default">
                                                <span class="bottom-0 bg-danger rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                                {{ \App\Enums\EstadoEnum::DESHABILITADO->descripcion() }}
                                            </span>
                                            @endif
                                        </td>

                                        <td>{{ formatoFechaText($item->au_fechacr)}}</td>

                                        <td class="text-center">
                                            <a class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm fs-6" data-bs-toggle="dropdown">
                                                Acciones
                                                <i class="ki-outline ki-down fs-5 ms-1"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-6 py-4" style="width: 180px;" data-kt-menu="true" x-data="{ cargando_opciones: false }" onclick="event.stopPropagation();">
                                                @if(!empty($permisos['MODIFICAR']))
                                                <div class="menu-item px-3" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" :class="{ 'item-disabled': cargando_opciones }">
                                                    <a class="menu-link px-3" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalUsuario', { id_usuario: {{ $item->id_usuario }} });" :disabled="cargando" :class="{ 'active': cargando }" onclick="event.stopPropagation();">
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
                                                    <a class="menu-link px-3" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEstado', { id_usuario: {{ $item->id_usuario }} });" :disabled="cargando" :class="{ 'active': cargando }" onclick="event.stopPropagation();">
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
                                                    <a class="menu-link px-3 menu-link-danger text-danger" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEliminar', { id_usuario: {{ $item->id_usuario }} });" :disabled="cargando" :class="{ 'active-danger': cargando }" onclick="event.stopPropagation();">
                                                        <span>Eliminar</span>
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
                            @if ($this->usuarios->hasPages())
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center">Mostrando {{ $this->usuarios->firstItem() }} - {{ $this->usuarios->lastItem() }} de {{ $this->usuarios->total() }} registros</div>
                                <div class="pagination pagination-lg">{{ $this->usuarios->links() }}</div>
                            </div>
                            @else
                            <div class="d-flex justify-content-between py-2">
                                <div class="d-flex align-items-center text-muted
                                        ">Mostrando {{ $this->usuarios->firstItem() }} - {{ $this->usuarios->lastItem() }} de {{ $this->usuarios->total() }} registros</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
