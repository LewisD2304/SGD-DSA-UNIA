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

                        <!-- Seleccionar persona -->
                        <div class="fv-row px-0" wire:ignore>
                            <label class="form-label fs-6 fw-bold mb-2">Buscar persona <span class="text-danger">*</span></label>
                            <select
                                id="idPersona"
                                class="form-select @if($errors->has('idPersona')) is-invalid @endif"
                                wire:model="idPersona"
                                data-control="select"
                                data-placeholder="Buscar persona"
                            >
                                <option value="">Buscar persona</option>
                                @forelse($this->listaPersona() as $persona)
                                    <option value="{{ $persona->id_persona }}">{{ strtoupper($persona->nombres_persona) }} {{ strtoupper($persona->apellido_paterno_persona) }} {{ strtoupper($persona->apellido_materno_persona) }}</option>
                                @empty
                                    <option value="" disabled>No hay personas</option>
                                @endforelse
                            </select>
                            @error('idPersona')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="fv-row px-0 mt-4" wire:ignore>
                            <label class="form-label fs-6 fw-bold mb-2">Rol Asignado <span class="text-danger">*</span></label>
                            <select
                                id="idRol"
                                class="form-select @if($errors->has('idRol')) is-invalid @endif"
                                wire:model="idRol"
                                data-control="select"
                                data-placeholder="Seleccione el rol"
                            >
                                <option value="">Seleccione el rol</option>
                                {{-- Recorre los roles desde el método computed listaRol() del componente --}}
                                @forelse($this->listaRol() as $rol)
                                    <option value="{{ $rol->id_rol }}">{{ $rol->nombre_rol }}</option>
                                @empty
                                    <option value="" disabled>No hay roles disponibles</option>
                                @endforelse
                            </select>
                            @error('idRol')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Datos de usuario -->
                        <div class="fw-bold text-dark mb-3 mt-4">
                            <i class="ki-outline ki-user me-2"></i> Datos de usuario
                        </div>

                        <div class="form-floating mb-3">
                            <input
                                type="text"
                                class="form-control text-uppercase @if ($errors->has('nombreUsuario')) is-invalid @elseif($nombreUsuario) is-valid @endif"
                                id="nombreUsuario"
                                autocomplete="off"
                                placeholder="Nombre de usuario"
                                wire:model.live="nombreUsuario"
                                maxlength="120"
                            />
                            <label for="nombreUsuario">
                                Nombre de usuario <span class="text-danger">*</span>
                            </label>
                            @error('nombreUsuario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Contraseña con toggle -->
                        <div class="form-floating" x-data="{ show: false }">
                            <input
                                :type="show ? 'text' : 'password'"
                                class="form-control @if ($errors->has('claveUsuario')) is-invalid @elseif($claveUsuario) is-valid @endif"
                                id="claveUsuario"
                                autocomplete="off"
                                placeholder="password"
                                wire:model.lazy="claveUsuario"
                                maxlength="80"
                            />
                            <label for="claveUsuario">Password <span class="text-danger">{{ $modoModal == 1 ? '*' : '' }}</span></label>
                            <div class="form-control-icon" style="right: 1rem; top: .8rem; position: absolute;">
                                <button type="button" class="btn btn-sm btn-icon btn-active-light" @click="show = !show" tabindex="-1">
                                    <i x-show="!show" class="ki-outline ki-eye fs-3"></i>
                                    <i x-show="show" class="ki-outline ki-eye-crossed fs-3"></i>
                                </button>
                            </div>
                            @error('claveUsuario')
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
