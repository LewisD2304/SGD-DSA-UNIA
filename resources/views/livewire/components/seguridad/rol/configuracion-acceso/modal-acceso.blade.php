<div wire:ignore.self class="modal fade" id="modal-acceso" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">
                    Asignar acceso
                </h3>

                <div
                    class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarAcceso">

                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-10">

                        <div class="d-flex flex-start mb-5">
                            <i class="ki-duotone ki-switch fs-1 px-0 text-primary">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="ms-2">
                                <label class="fs-6 fw-bold">
                                    Seleccionar menús
                                </label>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <tbody class="text-gray-600 fw-semibold">
                                    @if ($menus_seleccionados && $menus_seleccionados->count() === $menusDisponibles->count())
                                        <tr>
                                            <td
                                                colspan="2"
                                                class="text-center py-8 text-muted"
                                            >
                                                Todos los menús han sido asignados
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($menusDisponibles as $item)
                                            @if ($menus_seleccionados && !$menus_seleccionados->contains($item->id_menu))
                                                <tr>
                                                    <td class="text-gray-800">
                                                        <div class="d-flex flex-start align-items-center">
                                                            <div>
                                                                <label class="fs-6 fw-bold">
                                                                    MENÚ {{ $item->nombre_menu }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <label class="form-check form-switch form-check-custom form-check-solid">
                                                            <input
                                                                class="form-check-input cursor-pointer"
                                                                type="checkbox"
                                                                value="{{ $item->id_menu }}"
                                                                wire:model="menus_seleccionados"
                                                                name="menus_seleccionados"
                                                            />
                                                        </label>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
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
                        wire:target="guardarAcceso"
                    >
                        <span wire:loading.remove wire:target="guardarAcceso">
                            Guardar
                        </span>
                        <span wire:loading wire:target="guardarAcceso">
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
