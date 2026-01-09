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
                            <label class="form-label fs-6 fw-bold mb-2 required {{ $errors->has('idPersona') ? 'text-danger' : '' }}">
                                Buscar persona
                            </label>

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
                            <label class="form-label fs-6 fw-bold mb-2 required {{ $errors->has('idRol') ? 'text-danger' : '' }}">
                                Rol Asignado
                            </label>

                            <div wire:ignore>
                                <select id="idRol" class="form-select idRol" wire:model="idRol" style="width: 100%">
                                    <option value="">Seleccione el rol</option>
                                    @forelse($this->listaRol() as $rol)
                                    <option value="{{ $rol->id_rol }}">
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
                            <input type="text"
                                class="form-control text-uppercase @if($errors->has('nombreUsuario')) is-invalid @elseif($nombreUsuario) is-valid @endif"
                                id="nombreUsuario"
                                autocomplete="off"
                                placeholder="Nombre de usuario"
                                wire:model.live="nombreUsuario"
                                maxlength="120" />
                            <label for="nombreUsuario">Nombre de usuario <span class="text-danger">*</span></label>
                            @error('nombreUsuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-floating" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'"
                                class="form-control @if($errors->has('claveUsuario')) is-invalid @elseif($claveUsuario && $modoModal == 1) is-valid @endif"
                                id="claveUsuario"
                                autocomplete="off"
                                placeholder="password"
                                wire:model.lazy="claveUsuario"
                                maxlength="80" />

                            <label for="claveUsuario">
                                Password
                                {!! $modoModal == 1 ? '<span class="text-danger">*</span>' : '<span class="text-muted fs-8">(Opcional)</span>' !!}
                            </label>

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
                    <button type="button" class="btn d-flex align-items-center btn-light-secondary me-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn d-flex align-items-center btn-primary" wire:loading.attr="disabled" wire:target="guardarUsuario">
                        <span class="indicator-label" wire:loading.remove wire:target="guardarUsuario">Guardar</span>
                        <span class="indicator-progress" wire:loading wire:target="guardarUsuario">
                            Cargando... <span><x-spinner style="width: 20px; height: 20px;" /></span>
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
        // Configuración base para Select2
        const configSelect2 = (placeholder) => ({
            placeholder: placeholder,
            minimumResultsForSearch: 0,
            allowClear: true,
            dropdownParent: $('#modal-usuario'),
            width: '100%',
            language: {
                noResults: () => "No hay resultados",
                searching: () => "Buscando...",
            }
        });

        // Inicializar ID PERSONA
        if (!$('.idPersona').hasClass("select2-hidden-accessible")) {
            $('.idPersona').select2(configSelect2("Buscar persona"))
            .on('change', function (e) {
                @this.set('idPersona', $(this).val());
                // Validación visual manual si se desea
                if($(this).val()) {
                    $(this).next('.select2-container').find('.select2-selection').addClass('is-valid').removeClass('is-invalid');
                }
            });
        }

        // Inicializar ID ROL
        if (!$('.idRol').hasClass("select2-hidden-accessible")) {
            $('.idRol').select2(configSelect2("Seleccione el rol"))
            .on('change', function (e) {
                @this.set('idRol', $(this).val());
                if($(this).val()) {
                    $(this).next('.select2-container').find('.select2-selection').addClass('is-valid').removeClass('is-invalid');
                }
            });
        }
    };

    // Inicializar al cargar
    initSelect2Usuario();

    // Hook de Livewire
    document.addEventListener('livewire:initialized', () => {
        initSelect2Usuario();
    });

    // Reinicializar al abrir modal
    $('#modal-usuario').on('shown.bs.modal', function () {
        initSelect2Usuario();
    });

    // Limpiar Select2 al cerrar o resetear
    Livewire.on('limpiarSelect2', () => {
        $('.idPersona').val(null).trigger('change');
        $('.idRol').val(null).trigger('change');
        // Remover clases de validación al limpiar
        $('.select2-selection').removeClass('is-valid is-invalid');
    });

    // === AQUÍ ESTÁ LA MAGIA PARA CARGAR DATOS ===
    Livewire.on('cargarDatosModal', (data) => {
        // Livewire a veces envía los datos envueltos en un array
        const usuarioData = Array.isArray(data) ? data[0] : data;

        // Usamos un pequeño timeout para asegurar que el modal está listo
        setTimeout(() => {
            // Cargar nombre de usuario con validación visual
            if (usuarioData.nombreUsuario) {
                $('#nombreUsuario')
                    .val(usuarioData.nombreUsuario)
                    .removeClass('is-invalid')
                    .addClass('is-valid');
            }

            // Cargar persona
            if (usuarioData.idPersona) {
                $('.idPersona').val(usuarioData.idPersona).trigger('change');
            }

            // Cargar rol
            if (usuarioData.idRol) {
                $('.idRol').val(usuarioData.idRol).trigger('change');
            }
        }, 150);
    });
</script>
@endscript
