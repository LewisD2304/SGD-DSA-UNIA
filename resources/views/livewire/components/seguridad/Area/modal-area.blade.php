<div wire:ignore.self class="modal fade" id="modal-area" data-bs-backdrop="static" data-bs-keyboard="false">
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

            <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarArea">

                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-10">

                        <!-- INFORMACIÓN DEL ÁREA -->
                        <div class="fw-bold text-dark mb-3 mt-3">
                            <i class="ki-outline ki-abstract-47 me-2"></i> Información del área
                        </div>

                        <!-- Nombre del área -->
                        <div class="mb-3">
                            <div class="form-floating">
                                <input
                                    type="text"
                                    class="form-control text-uppercase @if ($errors->has('nombreArea')) is-invalid @elseif($nombreArea) is-valid @endif"
                                    id="nombreArea"
                                    autocomplete="off"
                                    placeholder="Nombre del área"
                                    wire:model.live="nombreArea"
                                    maxlength="100"
                                />
                                <label for="nombreArea">
                                    Nombre del área <span class="text-danger">*</span>
                                </label>
                                @error('nombreArea')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Siglas del área -->
                        <div class="mb-3">
                            <div class="form-floating">
                                <input
                                    type="text"
                                    class="form-control text-uppercase @if ($errors->has('siglasArea')) is-invalid @elseif($siglasArea) is-valid @endif"
                                    id="siglasArea"
                                    autocomplete="off"
                                    placeholder="Siglas"
                                    wire:model.live="siglasArea"
                                    maxlength="10"
                                />
                                <label for="siglasArea">
                                    Siglas <span class="text-danger">*</span>
                                </label>
                                @error('siglasArea')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Personas asignadas al área -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="ki-outline ki-profile-user me-1"></i>
                                Personas asignadas a esta área
                            </label>
                            <select
                                class="form-select form-select-solid"
                                wire:model="personasSeleccionadas"
                                id="personasSeleccionadas"
                                multiple
                                style="height: 150px;"
                            >
                                @forelse ($personasDisponibles as $persona)
                                    <option value="{{ $persona->id_persona }}">
                                        {{ $persona->nombres_persona }} {{ $persona->apellido_paterno_persona }} {{ $persona->apellido_materno_persona }}
                                    </option>
                                @empty
                                    <option disabled>No hay personas disponibles</option>
                                @endforelse
                            </select>
                            <div class="form-text text-muted">
                                <i class="ki-outline ki-information-5 me-1"></i>
                                Mantén presionado <strong>Ctrl</strong> (Windows) o <strong>Cmd</strong> (Mac) para seleccionar múltiples personas.
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
                        wire:target="guardarArea"
                    >
                        <span class="indicator-label" wire:loading.remove wire:target="guardarArea">
                            {{ $modoModal === 0 ? 'Registrar' : 'Actualizar' }}
                        </span>
                        <span class="indicator-progress" wire:loading wire:target="guardarArea">
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
