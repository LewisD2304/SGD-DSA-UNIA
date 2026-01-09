
<div wire:ignore.self class="modal fade" id="modal-permiso" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-1000px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">
                    Asignar permisos
                </h3>

                <div
                    class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarPermiso">

                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-8">

                        <div class="d-flex flex-start mb-5 align-items-center">
                            <i class="ki-duotone ki-check-square fs-1 px-0 text-primary">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="ms-2">
                                <label class="fs-6 fw-bold">
                                    Modificar permisos
                                </label>
                            </div>
                        </div>

                        <div class="table-responsive position-relative">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr>
                                        <th class="text-gray-800 fw-bold min-w-225px">
                                            Acceso administrador
                                            <span class="ms-1" data-bs-toggle="tooltip" aria-label="Permite un acceso completo al sistema" data-bs-original-title="Permite un acceso completo al sistema" data-kt-initialized="1">
                                                <i class="ki-outline ki-information-5 text-gray-800 fs-6 fw-bold"></i>
                                            </span>
                                        </th>
                                        <th>
                                            <label
                                                class="form-check form-check-sm form-check-custom form-check-solid me-9 cursor-pointer"
                                                wire:loading.class.remove="cursor-pointer"
                                                wire:loading.class="cursor-wait"
                                                wire:target="seleccionar_todo"
                                            >
                                                <input
                                                    class="form-check-input cursor-pointer"
                                                    type="checkbox"
                                                    wire:model.live="seleccionar_todo"
                                                    wire:loading.attr="disabled"
                                                    wire:loading.class.remove="cursor-pointer"
                                                    wire:loading.class="cursor-wait"
                                                    wire:target="seleccionar_todo"
                                                >
                                                <span
                                                    class="form-check-label cursor-pointer"
                                                    wire:loading.class.remove="cursor-pointer"
                                                    wire:loading.class="cursor-wait"
                                                    wire:target="seleccionar_todo"
                                                >
                                                    Seleccionar todo
                                                </span>
                                            </label>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="text-gray-600 fw-semibold"
                                    wire:loading.class="opacity-25 cursor-wait"
                                    wire:loading.attr="disabled"
                                    wire:target="seleccionar_todo"
                                >
                                   @forelse($this->menus as $item)
                                        <tr>
                                            <td class="text-gray-800">
                                                <div class="d-flex align-items-center gap-2" style="margin-left: {{ $item['nivel'] * 20 }}px">
                                                    @if($item['nivel'] > 0)
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-corner-down-right-double fs-3 text-muted">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M4 5v6a3 3 0 0 0 3 3h7" />
                                                            <path d="M10 10l4 4l-4 4m5 -8l4 4l-4 4" />
                                                        </svg>
                                                    @endif
                                                    {{ Str::ucfirst(Str::lower($item['nombre_menu'])) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    @forelse($item['acciones'] as $accion)
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-10 cursor-pointer">
                                                            <input
                                                                class="form-check-input cursor-pointer"
                                                                type="checkbox"
                                                                wire:model.live="acciones_seleccionadas.{{ $accion->id_accion }}"
                                                            >
                                                            <span class="form-check-label cursor-pointer">
                                                                {{ Str::ucfirst(Str::lower($accion->tipoAccion->descripcion_catalogo)) }}
                                                            </span>
                                                        </label>
                                                    @empty
                                                        <span class="text-muted">Sin acciones</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-8 text-muted">
                                                No hay registros en el sistema
                                            </td>
                                        </tr>
                                    @endforelse

                                    <div
                                        class="position-absolute top-50 start-50 translate-middle"
                                        style="margin-top: 1.06rem;"
                                        wire:loading
                                        wire:target="seleccionar_todo"
                                    >
                                        <span>
                                            <x-spinner class="text-primary" style="width: 35px; height: 35px;"/>
                                        </span>
                                    </div>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-center">
                    <button
                        type="reset"
                        class="btn d-flex align-items-center btn-light-secondary me-4"
                        data-bs-dismiss="modal"
                        aria-label="cancel"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn d-flex align-items-center btn-primary"
                        wire:loading.attr="disabled"
                        wire:target="guardarPermiso"
                    >
                        <span wire:loading.remove wire:target="guardarPermiso">
                            Guardar
                        </span>
                        <span wire:loading wire:target="guardarPermiso">
                            Cargando...
                            <span>
                                <x-spinner style="width: 20px; height: 20px;"/>
                            </span>
                        </span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
