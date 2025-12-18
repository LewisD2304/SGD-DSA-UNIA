<div wire:ignore.self class="modal fade" id="modal-usuario" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header placeholder-glow">
                <h3 class="fw-bold my-0">
                    {{ $tituloModal }}
                </h3>
                <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarUsuario">

                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-10">

                        <div class="fv-row px-0 mt-4">
                            <label class="form-label fs-6 fw-bold mb-2 required">Buscar persona <span class="text-danger"></span></label>

                            <div wire:ignore>
                                <select id="idPersona" class="form-select idPersona" wire:model="idPersona" data-placeholder="Buscar persona" style="width: 100%">
                                    <option value="">Buscar persona</option>
                                    @forelse($this->listaPersona() as $persona)
                                    <option value="{{ $persona->id_persona }}">
                                        {{ strtoupper($persona->nombres_persona) }} {{ strtoupper($persona->apellido_paterno_persona) }} {{ strtoupper($persona->apellido_materno_persona) }}
                                    </option>
                                    @empty
                                    <option value="" disabled>No hay personas registradas</option>
                                    @endforelse
                                </select>
                            </div>
                            @error('idPersona')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="fv-row px-0 mt-4">
                            <label class="form-label fs-6 fw-bold mb-2 required">Rol Asignado <span class="text-danger"></span></label>

                            <div wire:ignore>
                                <select id="idRol" class="form-select idRol" wire:model="idRol">
                                    <option value="">Seleccione el rol</option>
                                    @forelse($this->listaRol() as $rol)
                                    <option value="{{ $rol->id_rol }}" {{ $idRol == $rol->id_rol ? 'selected' : '' }}>
                                        {{ $rol->nombre_rol }}
                                    </option>
                                    @empty
                                    <option value="" disabled>No hay roles disponibles</option>
                                    @endforelse
                                </select>
                            </div>
                            @error('idRol')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="fw-bold text-dark mb-3 mt-4">
                            <i class="ki-outline ki-user me-2"></i> Datos de usuario
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control text-uppercase @if ($errors->has('nombreUsuario')) is-invalid @elseif($nombreUsuario) is-valid @endif" id="nombreUsuario" autocomplete="off" placeholder="Nombre de usuario" wire:model.live="nombreUsuario" maxlength="120" />
                            <label for="nombreUsuario">Nombre de usuario <span class="text-danger">*</span></label>
                            @error('nombreUsuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-floating" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" class="form-control @if ($errors->has('claveUsuario')) is-invalid @elseif($claveUsuario) is-valid @endif" id="claveUsuario" autocomplete="off" placeholder="password" wire:model.lazy="claveUsuario" maxlength="80" />
                            <label for="claveUsuario">Password <span class="text-danger">{{ $modoModal == 1 ? '*' : '' }}</span></label>
                            <div class="form-control-icon" style="right: 1rem; top: .8rem; position: absolute;">
                                <button type="button" class="btn btn-sm btn-icon btn-active-light" @click="show = !show" tabindex="-1">
                                    <i x-show="!show" class="ki-outline ki-eye fs-3"></i>
                                    <i x-show="show" class="ki-outline ki-eye-crossed fs-3"></i>
                                </button>
                            </div>
                            @error('claveUsuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-center">
                    <button type="reset" class="btn d-flex align-items-center btn-light-secondary me-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn d-flex align-items-center btn-primary" wire:loading.attr="disabled" wire:target="guardarUsuario">
                        <span class="indicator-label" wire:loading.remove wire:target="guardarUsuario">Guardar</span>
                        <span class="indicator-progress" wire:loading wire:target="guardarUsuario">
                            Cargando... <span>
                                <x-spinner style="width: 20px; height: 20px;" /></span>
                        </span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@script
<script>
    const initSelect2Usuario = () => {
        // Configuración común
        const configSelect2 = (placeholder) => ({
            placeholder: placeholder,
            minimumResultsForSearch: 0, // Siempre mostrar buscador
            allowClear: true,
            dropdownParent: $('#modal-usuario'),
            width: '100%',
            language: {
                noResults: () => "No hay resultados",
                searching: () => "Buscando...",
            }
        });

        // 1. Inicializar ID PERSONA
        // Solo inicializamos si NO tiene la clase select2-hidden-accessible (significa que aún no es select2)
        if (!$('.idPersona').hasClass("select2-hidden-accessible")) {
            $('.idPersona').select2(configSelect2("Buscar persona"))
            .on('change', function (e) {
                @this.set('idPersona', $(this).val());
            });
        }

        // 2. Inicializar ID ROL
        if (!$('.idRol').hasClass("select2-hidden-accessible")) {
            $('.idRol').select2(configSelect2("Seleccione el rol"))
            .on('change', function (e) {
                @this.set('idRol', $(this).val());
            });
        }
    };

    // Inicializar al cargar la página
    initSelect2Usuario();

    // Inicializar cuando Livewire termina de cargar por primera vez
    document.addEventListener('livewire:initialized', () => {
        initSelect2Usuario();
    });

    // REGLA DE ORO: Al abrir el modal, forzamos la inicialización
    // pero NO destruimos si ya existe, solo nos aseguramos de que esté ahí.
    $('#modal-usuario').on('shown.bs.modal', function () {
        initSelect2Usuario();
    });

    // Escuchar limpieza desde backend (solo limpiar valor, no destruir)
    Livewire.on('limpiarSelect2', () => {
        $('.idPersona').val(null).trigger('change');
        $('.idRol').val(null).trigger('change');
    });
</script>
@endscript
