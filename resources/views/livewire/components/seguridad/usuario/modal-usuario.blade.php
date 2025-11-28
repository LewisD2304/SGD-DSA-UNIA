<div wire:ignore.self class="modal fade" id="modal-usuario" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header placeholder-glow">
                <h3 class="fw-bold my-0">
                    {{ $tituloModal }}
                </h3>

                <div
                    class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarUsuario">

                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-10">

                        <div class="form-floating">
                            <input
                                type="text"
                                class="form-control text-uppercase @if ($errors->has('nombreUsuario')) is-invalid @elseif($nombreUsuario) is-valid @endif"
                                id="nombreUsuario"
                                autocomplete="off"
                                placeholder="Nombre del usuario"
                                wire:model.live="nombreUsuario"
                                maxlength="60"
                            />
                            <label for="nombreUsuario">
                                Nombre del usuario <span class="text-danger">*</span>
                            </label>
                            @error('nombreUsuario')
                                <div class="invalid-feedback">{{ $message }}</div>
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
                        wire:target="guardarUsuario"
                    >
                        <span class="indicator-label" wire:loading.remove wire:target="guardarUsuario">
                            Guardar
                        </span>
                        <span class="indicator-progress" wire:loading wire:target="guardarUsuario">
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
