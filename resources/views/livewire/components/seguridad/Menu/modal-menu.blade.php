<div wire:ignore.self class="modal fade" id="modal-menu" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">
                    {{ $titulo_modal }}
                </h3>
                <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarMenu">

                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-10">

                        <div class="alert alert-dismissible bg-light-warning border border-warning border-2 border-dashed d-flex flex-column flex-sm-row p-5 mb-7">
                            <i class="ki-duotone ki-information fs-2hx text-warning me-4 mb-5 mb-sm-0">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>

                            <div class="d-flex flex-column pe-0 pe-sm-5">
                                <h4 class="mb-1">Importante</h4>

                                <span class="fs-6 fw-normal text-gray-700">
                                    Asegúrese que el valor ingresado en  el campo "RUTA DEL MENÚ", contenga el formato correcto ya que una ruta incorrecta podría afectar la organización y acceso a los elementos del sistema.
                                </span>
                            </div>
                        </div>

                        <div class="form-floating mb-7">
                            <input
                                type="text"
                                class="form-control text-uppercase @if ($errors->has('nombre_menu')) is-invalid @elseif($nombre_menu) is-valid @endif"
                                id="nombre_menu"
                                autocomplete="off"
                                placeholder="Nombre del módulo"
                                wire:model.live="nombre_menu"
                                maxlength="60"
                            />
                            <label for="nombre_menu text-grey-600">
                                Nombre del menú <span class="text-danger">*</span>
                            </label>
                            @error('nombre_menu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-7">
                            <input
                                type="text"
                                class="form-control text-uppercase @if ($errors->has('ruta_menu')) is-invalid @elseif($ruta_menu) is-valid @endif"
                                id="ruta_menu"
                                autocomplete="off"
                                placeholder="Ruta del menú"
                                wire:model.live="ruta_menu"
                                maxlength="60"
                            />
                            <label for="ruta_menu text-grey-600">
                                Ruta del menú <span class="text-danger">*</span>
                            </label>
                            @error('ruta_menu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <div class="form-floating" wire:ignore>
                                <select class="form-select acciones"
                                    id="acciones"
                                    aria-label="Acciones"
                                    wire:model="acciones"
                                >
                                    @foreach ($this->accionesMenu as $item)
                                        <option value="{{ $item->id_catalogo }}">
                                            {{ $item->descripcion_catalogo }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="acciones" class="required">
                                    Acciones <span class="text-danger">*</span>
                                </label>
                            </div>

                            @error('acciones')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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
                        wire:target="guardarMenu"
                    >
                        <span wire:loading.remove wire:target="guardarMenu">
                            Guardar
                        </span>
                        <span wire:loading wire:target="guardarMenu">
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
