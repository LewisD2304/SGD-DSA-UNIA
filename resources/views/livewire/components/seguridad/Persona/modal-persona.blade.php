<div wire:ignore.self class="modal fade" id="modal-persona" data-bs-backdrop="static" data-bs-keyboard="false">
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

            <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarPersona">

                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-10">

                        <!-- INFORMACIÓN PERSONAL -->
                        <div class="fw-bold text-dark mb-3 mt-3">
                            <i class="ki-outline ki-profile-user me-2"></i> Información personal
                        </div>

                        <!-- Tipo documento y Número documento -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input
                                        type="text"
                                        class="form-control text-uppercase @if ($errors->has('numerodocumentoPersona')) is-invalid @elseif($numerodocumentoPersona) is-valid @endif"
                                        id="numerodocumentoPersona"
                                        autocomplete="off"
                                        placeholder="Número documento"
                                        wire:model.live="numerodocumentoPersona"
                                        maxlength="20"
                                        inputmode="numeric"
                                        @keypress="if (!/[0-9]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') event.preventDefault()"
                                    />
                                    <label for="numerodocumentoPersona">
                                        Número documento <span class="text-danger">*</span>
                                    </label>
                                    @error('numerodocumentoPersona')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input
                                        type="text"
                                        class="form-control text-uppercase @if ($errors->has('nombresPersona')) is-invalid @elseif($nombresPersona) is-valid @endif"
                                        id="nombresPersona"
                                        autocomplete="off"
                                        placeholder="Nombres"
                                        wire:model.live="nombresPersona"
                                        maxlength="60"
                                        @keypress="if (!/[A-Za-z\s]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') event.preventDefault()"
                                    />
                                    <label for="nombresPersona">
                                        Nombres <span class="text-danger">*</span>
                                    </label>
                                    @error('nombresPersona')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- ¿Tiene un solo apellido? -->
                        <div class="mb-3">
                            <label class="form-check form-check-inline">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="soloApellido"
                                    wire:model.live="soloApellido"
                                />
                                <span class="form-check-label">
                                    ¿Tiene un solo apellido?
                                </span>
                            </label>
                        </div>

                        <!-- Apellidos -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input
                                        type="text"
                                        class="form-control text-uppercase @if ($errors->has('apellidoPaternoPersona')) is-invalid @elseif($apellidoPaternoPersona) is-valid @endif"
                                        id="apellidoPaternoPersona"
                                        autocomplete="off"
                                        placeholder="Apellido paterno"
                                        wire:model.live="apellidoPaternoPersona"
                                        maxlength="60"
                                        @keypress="if (!/[A-Za-z\s]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') event.preventDefault()"
                                    />
                                    <label for="apellidoPaternoPersona">
                                        Apellido paterno <span class="text-danger">*</span>
                                    </label>
                                    @error('apellidoPaternoPersona')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if(!$soloApellido)
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input
                                            type="text"
                                            class="form-control text-uppercase @if ($errors->has('apellidoMaternoPersona')) is-invalid @elseif($apellidoMaternoPersona) is-valid @endif"
                                            id="apellidoMaternoPersona"
                                            autocomplete="off"
                                            placeholder="Apellido materno"
                                            wire:model.live="apellidoMaternoPersona"
                                            maxlength="60"
                                            @keypress="if (!/[A-Za-z\s]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') event.preventDefault()"
                                        />
                                        <label for="apellidoMaternoPersona">
                                            Apellido materno <span class="text-danger">*</span>
                                        </label>
                                        @error('apellidoMaternoPersona')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- INFORMACIÓN DE CONTACTO -->
                        <div class="fw-bold text-dark mb-3 mt-5">
                            <i class="ki-outline ki-phone me-2"></i> Información de contacto
                        </div>

                        <!-- Celular y Correo -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input
                                        type="text"
                                        class="form-control @if ($errors->has('celularPersona')) is-invalid @elseif($celularPersona) is-valid @endif"
                                        id="celularPersona"
                                        autocomplete="off"
                                        placeholder="Celular"
                                        wire:model.live="celularPersona"
                                        maxlength="20"
                                        inputmode="numeric"
                                        @keypress="if (!/[0-9]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') event.preventDefault()"
                                    />
                                    <label for="celularPersona">
                                        Celular
                                    </label>
                                    @error('celularPersona')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input
                                        type="email"
                                        class="form-control @if ($errors->has('correoPersona')) is-invalid @elseif($correoPersona) is-valid @endif"
                                        id="correoPersona"
                                        autocomplete="off"
                                        placeholder="Correo"
                                        wire:model.live="correoPersona"
                                        maxlength="120"
                                    />
                                    <label for="correoPersona">
                                        Correo
                                    </label>
                                    @error('correoPersona')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
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
                        wire:target="guardarPersona"
                    >
                        <span class="indicator-label" wire:loading.remove wire:target="guardarPersona">
                            Guardar
                        </span>
                        <span class="indicator-progress" wire:loading wire:target="guardarPersona">
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
