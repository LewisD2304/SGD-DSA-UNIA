<div class="card" x-data="{ cargando_opciones: false }">
    <div class="card-header">
        <h3 class="card-title fw-bold">
            <i class="ki-outline ki-cube-3 fs-1 text-primary me-2"></i>
            Tabla
        </h3>
        <div class="card-toolbar gap-2">
            @if(!empty($permisos['MODIFICAR']))
                <button
                    type="button"
                    class="btn btn-sm btn-icon btn-active-light-primary"
                    x-data="{ cargando: false }"
                    @cargando.window="cargando = false, cargando_opciones = false"
                    @click="cargando = true; cargando_opciones = true; $dispatch('cargar_modal_tabla', { id_padre: {{ $id_padre }} });"
                    :disabled="cargando_opciones"
                    :class="{ 'active': cargando }"
                >
                    <template x-if="!cargando">
                        <i class="ki-outline ki-notepad-edit fs-1"></i>
                    </template>
                    <template x-if="cargando">
                        <span>
                            <x-spinner style="width: 20px; height: 20px;"/>
                        </span>
                    </template>
                </button>
            @endif

            @if(!empty($permisos['CAMBIAR ESTADO']))
                <button
                    x-data
                    type="button"
                    class="btn btn-sm btn-icon btn-active-light-primary"
                    @cargando.window="cargando_opciones = false"
                    @click="cargando_opciones = true;"
                    :disabled="cargando_opciones"
                    wire:click="cargar_modal_estado_padre"
                    wire:loading.attr="disabled"
                    wire:loading.class="active"
                    wire:target="cargar_modal_estado_padre"
                >
                    <i
                        class="ki-outline ki-lock fs-1"
                        wire:loading.remove
                        wire:target="cargar_modal_estado_padre"
                    ></i>
                    <span
                        wire:loading
                        wire:target="cargar_modal_estado_padre"
                    >
                        <x-spinner class="text-primary" />
                    </span>
                </button>
            @endif

            @if(!empty($permisos['REGISTRAR']))
                <button
                    x-data
                    type="button"
                    class="btn btn-sm btn-icon btn-active-light-primary"
                    @cargando.window="cargando_opciones = false"
                    @click="cargando_opciones = true;"
                    :disabled="cargando_opciones"
                    wire:click="cargar_modal_item({{ null }})"
                    wire:loading.attr="disabled"
                    wire:loading.class="active"
                    wire:target="cargar_modal_item({{ null }})"
                >
                    <i
                        class="ki-outline ki-plus-square fs-1"
                        wire:loading.remove
                        wire:target="cargar_modal_item({{ null }})"
                    ></i>
                    <span
                        wire:loading
                        wire:target="cargar_modal_item({{ null }})"
                    >
                        <x-spinner class="text-primary" />
                    </span>
                </button>
            @endif
        </div>
    </div>
    <div class="card-body py-4">
        <div lass="dataTables_wrapper dt-bootstrap4 no-footer">
            <div class="table-responsive position-relative">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                    <thead>
                        <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                            <th class="w-10px pe-2">N°</th>
                            <th class="min-w-125px">DESCRIPCIÓN LARGA</th>
                            <th class="min-w-125px">DESCRIPCIÓN CORTA</th>
                            <th class="min-w-125px">ESTADO</th>
                            <th class="text-center w-100px">ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody
                        class="text-gray-600 fw-semibold"
                        wire:loading.class="opacity-25"
                        wire:target="lista_hijos, id_padre, previousPage, nextPage, gotoPage"
                    >
                        @php
                            $contador = $this->lista_hijos->firstItem();
                        @endphp
                        @forelse ($this->lista_hijos as $item)
                            <tr wire:key="{{ $item->id_catalogo }}">
                                <td>{{ $contador++ }}</td>
                                <td>{{ $item->descripcion_catalogo }}</td>
                                <td>{{ $item->abreviatura_catalogo }}</td>
                                <td>
                                    @if ($item->estado_catalogo === \App\Enums\EstadoEnum::HABILITADO)
                                        <span class="badge py-2 px-4 fs-6 fw-normal badge-light-success cursor-default">
                                            <span class="bottom-0 bg-success rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                            {{ \App\Enums\EstadoEnum::HABILITADO->descripcion() }}
                                        </span>
                                    @elseif ($item->estado_catalogo === \App\Enums\EstadoEnum::DESHABILITADO)
                                        <span class="badge py-2 px-4 fs-6 fw-normal badge-light-danger cursor-default">
                                            <span class="bottom-0 bg-danger rounded-circle border border-4 border-body h-20px w-20px me-2"></span>
                                            {{ \App\Enums\EstadoEnum::DESHABILITADO->descripcion() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!empty($permisos) && collect($permisos)->except('REGISTRAR')->filter()->isNotEmpty())
                                        <div class="d-flex justify-content-center gap-2">
                                            @if(!empty($permisos['MODIFICAR']))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-icon btn-active-light-primary"
                                                    x-on:click="$wire.cargar_modal_item({{ $item->id_catalogo }})"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <i
                                                        class="ki-outline ki-notepad-edit fs-1"
                                                        wire:loading.remove
                                                        wire:target="cargar_modal_item({{ $item->id_catalogo }})"
                                                    ></i>
                                                    <span
                                                        wire:loading
                                                        wire:target="cargar_modal_item({{ $item->id_catalogo }})"
                                                    >
                                                        <x-spinner class="text-primary" />
                                                    </span>
                                                </button>
                                            @endif

                                            @if(!empty($permisos['CAMBIAR ESTADO']))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-icon btn-active-light-primary"
                                                    x-on:click="$wire.cargar_modal_estado_hijo({{ $item->id_catalogo }})"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <i
                                                        class="ki-outline ki-lock fs-1"
                                                        wire:loading.remove
                                                        wire:target="cargar_modal_estado_hijo({{ $item->id_catalogo }})"
                                                    ></i>
                                                    <span
                                                        wire:loading
                                                        wire:target="cargar_modal_estado_hijo({{ $item->id_catalogo }})"
                                                    >
                                                        <x-spinner class="text-primary" />
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="d-flex justify-content-center">
                                            <span class="badge badge-light-secondary fw-normal text-muted fs-6 px-3 rounded-pill cursor-default">
                                                Sin opciones
                                            </span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="text-center py-8 text-muted"
                                >
                                    <!-- Mostrar mensaje si no hay registros -->
                                    <div
                                        x-data="{ cargado: false, modo: localStorage.getItem('data-bs-theme-mode') || 'light' }"
                                        x-init="cargado = true"
                                    >
                                        <template x-if="cargado">
                                            <x-blank-state-table mensaje="No se encontraron registros"/>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <div
                        class="position-absolute top-50 start-50 translate-middle"
                        style="margin-top: 1.06rem;"
                        wire:loading
                        wire:target="lista_hijos, id_padre, previousPage, nextPage, gotoPage"
                    >
                        <x-spinner class="text-primary" style="width: 35px; height: 35px;" />
                    </div>
                </table>
                <div>
                    @if ($this->lista_hijos->hasPages())
                        <div class="d-flex justify-content-between">
                            <div class="d-flex align-items-center">Mostrando {{ $this->lista_hijos->firstItem() }} - {{ $this->lista_hijos->lastItem() }} de {{ $this->lista_hijos->total() }} registros</div>
                            <div class="pagination pagination-lg">{{ $this->lista_hijos->links(data: ['scrollTo' => false]) }}</div>
                        </div>
                    @else
                        <div class="d-flex justify-content-between py-2">
                            <div class="d-flex align-items-center text-muted
                            ">Mostrando {{ $this->lista_hijos->firstItem() }} - {{ $this->lista_hijos->lastItem() }} de {{ $this->lista_hijos->total() }} registros</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar el estado de la tabla -->
    <div wire:ignore.self class="modal fade" id="modal-estado-catalogo" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="fw-bold my-0">
                        {{ $titulo_modal_estado }}
                    </h3>
                    <div
                        class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    >
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <form autocomplete="off" class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="estado_catalogo">

                    <div class="modal-body px-5">
                        <div class="d-flex flex-column px-5">
                            <div class="modal-header text-center flex-column border-0">
                                <p>
                                    <i class="ki-duotone ki-lock text-{{ $modo_catalogo_estado === 1 ? 'danger' : 'success' }}" style="font-size: 7rem !important;">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </p>
                                <h4 class="modal-title w-100 mt-5">
                                    ¿Estás seguro de que deseas {{ $modo_catalogo_estado === 1 ? 'deshabilitar' : 'habilitar' }} esta tabla?
                                </h4>
                            </div>

                            <div class="px-4 text-center fs-5">
                                <p class="text-gray-700">
                                    @if ($modo_catalogo_estado === 1)
                                        @if ($modo_catalogo === 1)
                                            Al deshabilitar esta tabla, todos los elementos dejarán de estar disponibles en los formularios. Sin embargo, su información se mantendrá
                                            almacenada y podrás volver a habilitarla en cualquier momento.
                                        @else
                                            Al deshabilitar este elemento, todos los elementos dejarán de estar disponibles en los formularios. Sin embargo, su información se mantendrá
                                            almacenada y podrás volver a habilitarla en cualquier momento.
                                        @endif
                                    @elseif ($modo_catalogo_estado === 2)
                                        Al habilitar este registro, estará disponible en el sistema y podrás realizar operaciones con él.
                                    @endif
                                </p>

                                <div class="d-flex justify-content-center mt-7">
                                    <div class="fw-bold">Registro:</div>
                                    <div class="px-2 text-gray-700 text-start">{{ $nombre_catalogo_estado }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-center">
                        <button
                            type="button"
                            class="btn d-flex align-items-center btn-light-secondary me-4"
                            data-bs-dismiss="modal"
                            aria-label="Cancelar"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="btn d-flex align-items-center btn-{{ $modo_catalogo_estado === 1 ? 'danger' : 'success' }}"
                            wire:loading.attr="disabled"
                            wire:target="estado_catalogo"
                            @click="$dispatch('cargando_padre', { cargando: true, modo_catalogo: {{ $modo_catalogo }} })"
                        >
                            <span wire:loading.remove wire:target="estado_catalogo">
                                {{ $modo_catalogo_estado === 1 ? 'Deshabilitar' : 'Habilitar' }}
                            </span>
                            <span wire:loading wire:target="estado_catalogo">
                                Cargando... <x-spinner style="width: 20px; height: 20px;" />
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para agregar o editar un item -->
    <div wire:ignore.self class="modal fade" id="modal-item" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">

                <div class="modal-header placeholder-glow">
                    <h3 class="fw-bold my-0">
                        {{ $titulo_modal }}
                    </h3>
                    <div
                        class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    >
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardar_item">

                    <div class="modal-body px-5">
                        <div class="d-flex flex-column px-5 px-lg-10">

                            <div class="form-floating mb-7">
                                <input
                                    type="text"
                                    class="form-control text-uppercase @if ($errors->has('descripcion_larga')) is-invalid @elseif($descripcion_larga) is-valid @endif"
                                    id="descripcion_larga"
                                    autocomplete="off"
                                    placeholder="Descripción larga"
                                    wire:model.live="descripcion_larga"
                                    maxlength="100"
                                />
                                <label for="descripcion_larga">
                                    Descripción larga <span class="text-danger">*</span>
                                </label>
                                @error('descripcion_larga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-7">
                                <input
                                    type="text"
                                    class="form-control text-uppercase @if ($errors->has('descripcion_corta')) is-invalid @elseif($descripcion_corta) is-valid @endif"
                                    id="descripcion_corta"
                                    autocomplete="off"
                                    placeholder="Descripción corta"
                                    wire:model.live="descripcion_corta"
                                    maxlength="100"
                                />
                                <label for="descripcion_corta">
                                    Descripción corta <span class="text-danger">*</span>
                                </label>
                                @error('descripcion_corta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-center">
                        <button
                            type="button"
                            class="btn d-flex align-items-center btn-light-secondary me-4"
                            data-bs-dismiss="modal"
                            aria-label="Cancelar"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="btn d-flex align-items-center btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="guardar_item"
                        >
                            <span class="indicator-label" wire:loading.remove wire:target="guardar_item">
                                Guardar
                            </span>
                            <span class="indicator-progress" wire:loading wire:target="guardar_item">
                                Cargando... <x-spinner style="width: 20px; height: 20px;" />
                            </span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>
