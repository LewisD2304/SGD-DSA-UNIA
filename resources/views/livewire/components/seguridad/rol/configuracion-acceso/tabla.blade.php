<div>
    <div class="app-container container-fluid">
        <div class="card" x-data="{ cargando_opciones: false }" @cargando.window="cargando_opciones = false">
            <div class="card-header">
                <h3 class="card-title fw-bold">
                    <i class="ki-duotone ki-element-8 fs-1 text-primary me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <span class="d-none d-sm-inline me-1">
                        Menús asignados -
                    </span>
                    {{ $modelo_rol->nombre_rol }}
                </h3>

                <div class="card-toolbar gap-2">
                    <a href="{{ route('seguridad.rol.index') }}" class="btn btn-light btn-active-light-primary px-4 px-md-6 me-2 me-md-4">
                        <i class="ki-outline ki-arrow-left fs-4 px-0"></i>
                        <span class="d-none d-md-inline">
                            Regresar
                        </span>
                    </a>

                    <button type="button" class="btn btn-primary px-4 px-sm-6" x-data="{ cargando: false }" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalAcceso', { menus_seleccionados: {{ $menus_seleccionados }} });" @cargando.window="cargando = false, cargando_opciones = false" :disabled="cargando">
                        <template x-if="!cargando">
                            <i class="ki-outline ki-plus fs-2 px-0"></i>
                        </template>
                        <template x-if="cargando">
                            <span>
                                <x-spinner style="width: 20px; height: 20px;" />
                            </span>
                        </template>
                        <span class="d-none d-sm-inline">
                            Asignar
                        </span>
                    </button>
                </div>
            </div>

            <div class="card-body py-4">
                <div lass="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="table-responsive position-relative">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                            <thead>
                                <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                    <th class="min-w-125px">NOMBRE DEL MENÚ</th>
                                    <th class="text-center min-w-60px">ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600" wire:loading.class="opacity-25" wire:target="buscar, gotoPage, previousPage, nextPage">

                                @if ($this->menusAsignados->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-center py-8 text-muted">
                                        No hay registros en el sistema
                                    </td>
                                </tr>
                                @else
                                @forelse ($this->menusAsignados as $item)
                                <tr wire:key="{{ $item->id_menu }}">
                                    <td>{{ $item->nombre_menu }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" x-data="{ cargando: false }" title="Asignar permisos" @cargando.window="cargando = false, cargando_opciones = false" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalPermiso', { id_menu: {{ $item->id_menu }} });" :disabled="cargando_opciones" :class="{ 'active': cargando}">
                                                <template x-if="!cargando">
                                                    <i class="ki-outline ki-setting-3 fs-1"></i>
                                                </template>
                                                <template x-if="cargando">
                                                    <span>
                                                        <x-spinner />
                                                    </span>
                                                </template>
                                            </button>
                                            @if ($item->fecha_primer_permiso)
                                            <button type="button" class="btn btn-sm btn-icon btn-active-light-danger" title="Eliminar permiso" x-data="{ cargando: false }" @cargando.window="cargando = false, cargando_opciones = false" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEliminarPermiso', { id_menu: {{ $item->id_menu }} });" :disabled="cargando_opciones" :class="{ 'active': cargando }">
                                                <template x-if="!cargando">
                                                    <i class="ki-outline ki-trash fs-1"></i>
                                                </template>
                                                <template x-if="cargando">
                                                    <span>
                                                        <x-spinner /></span>
                                                </template>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-icon btn-active-light-danger" x-data="{ cargando: false }" @cargando.window="cargando = false" @click="cargando = true; cargando_opciones = true; $dispatch('abrirModalEliminarMenu', { id_menu: {{ $item->id_menu }} });" :disabled="cargando_opciones" :class="{ 'active': cargando }">
                                                <template x-if="!cargando">
                                                    <i class="ki-outline ki-trash fs-1"></i>
                                                </template>
                                                <template x-if="cargando">
                                                    <span>
                                                        <x-spinner />
                                                    </span>
                                                </template>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-8 text-muted">
                                        No hay registros en el sistema
                                    </td>
                                </tr>
                                @endforelse
                                @endif
                            </tbody>
                        </table>
                        <div class="position-absolute top-50 start-50 translate-middle" style="margin-top: 1.06rem;" wire:loading wire:target="buscar">
                            <x-spinner class="text-primary" style="width: 35px; height: 35px;" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
