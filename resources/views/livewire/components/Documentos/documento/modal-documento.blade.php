@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div wire:ignore.self class="modal fade" id="modal-documento" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <div class="modal-header placeholder-glow">
                <h3 class="fw-bold my-0">
                    {{ $tituloModal }}
                </h3>

                <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarDocumento">

                <div class="modal-body px-5 py-4">
                    <div class="px-3">

                        {{-- Información del documento --}}
                        <div class="fw-bold text-dark mb-4 d-flex align-items-center">
                            <i class="ki-outline ki-document me-2 fs-3"></i>
                            <span>Información del documento</span>
                        </div>

                        {{-- Fila 1: Número y Folio --}}
                        <div class="row g-4 mb-4">
                            <div class="col-md-8">
                                <div class="form-floating">
                                    <input type="text" class="form-control text-uppercase @error('numeroDocumento') is-invalid @elseif($numeroDocumento) is-valid @enderror" id="numeroDocumento" autocomplete="off" placeholder="Ej: CARTA MULTIPLE Nº 004-2025-UNIA-VRA/DSA" wire:model.live="numeroDocumento" maxlength="100" />
                                    <label for="numeroDocumento">
                                        Número documento <span class="text-danger">*</span>
                                    </label>
                                    @error('numeroDocumento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="number" class="form-control @error('folioDocumento') is-invalid @elseif($folioDocumento) is-valid @enderror" id="folioDocumento" autocomplete="off" placeholder="Folio" wire:model.live="folioDocumento" min="1" step="1" @keydown.minus.prevent />

                                    <label for="folioDocumento">
                                        Folio
                                        @if(!empty($archivosDocumento) || !empty($archivosExistentes))
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    @error('folioDocumento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Fila 2: Asunto --}}
                        <div class="mb-4">
                            <div class="form-floating">
                                <textarea class="form-control text-uppercase @error('asuntoDocumento') is-invalid @elseif($asuntoDocumento) is-valid @enderror" id="asuntoDocumento" autocomplete="off" placeholder="Asunto" wire:model.live="asuntoDocumento" maxlength="200" style="height: 85px" @keypress="if (!/[A-Za-z0-9\s,.]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') event.preventDefault()"></textarea>
                                <label for="asuntoDocumento">
                                    Asunto <span class="text-danger">*</span>
                                </label>
                                @error('asuntoDocumento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Fila 3: Tipo de Documento y Oficina Remitente --}}
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="tipoDocumentoCatalogo" class="form-label required fw-semibold">
                                    Tipo de Documento
                                </label>
                                <div wire:ignore>
                                    <select class="form-select tipoDocumentoCatalogo" id="tipoDocumentoCatalogo" wire:model="tipoDocumentoCatalogo">
                                        <option value="">Seleccione un tipo de documento</option>
                                        @foreach($tiposDocumento as $tipo)
                                            <option value="{{ $tipo->id_catalogo }}" {{ $tipoDocumentoCatalogo == $tipo->id_catalogo ? 'selected' : '' }}>{{ $tipo->descripcion_catalogo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('tipoDocumentoCatalogo')
                                    <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="oficina" class="form-label required fw-semibold">
                                    Oficina Remitente
                                </label>
                                <div wire:ignore>
                                    <select class="form-select oficina" id="oficina" wire:model="oficina">
                                        <option value="">Seleccione la oficina</option>
                                        @foreach($oficinas as $of)
                                            <option value="{{ $of->id_catalogo }}" data-abreviatura="{{ $of->abreviatura_catalogo }}">
                                                {{ ($of->abreviatura_catalogo ? $of->abreviatura_catalogo . ' - ' : '') . $of->descripcion_catalogo }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('oficina')
                                    <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if ($modoModal === 2)
                        <div class="mb-4">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-light" id="areaRemitente" readonly value="{{ auth()->user()->persona->area->nombre_area ?? 'Sin área asignada' }}" />
                                <label for="areaRemitente">
                                    Remitente (Área)
                                </label>
                            </div>
                        </div>
                        @endif

                        {{-- Fila 4: Área Destino --}}
                        <div class="mb-4">
                            <label for="idAreaDestino" class="form-label required fw-semibold">
                                Área Destino
                            </label>
                            <div wire:ignore>
                                <select class="form-select idAreaDestino" id="idAreaDestino" wire:model="idAreaDestino">
                                    <option value="">Seleccione un destino</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}" {{ $idAreaDestino == $area->id_area ? 'selected' : '' }}>{{ $area->nombre_area }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('idAreaDestino')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fila 5: Observación y Mensaje de Derivación en columnas --}}
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <textarea class="form-control text-uppercase @error('observacionDocumento') is-invalid @enderror" id="observacionDocumento" autocomplete="off" placeholder="Observación" wire:model="observacionDocumento" maxlength="500" style="height: 85px"></textarea>
                                    <label for="observacionDocumento">
                                        Observación (Opcional)
                                    </label>
                                    @error('observacionDocumento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <textarea
                                        class="form-control @error('comentarioDerivacionNuevo') is-invalid @enderror"
                                        id="comentarioDerivacionNuevo"
                                        placeholder="Respuesta o mensaje de derivación"
                                        wire:model="comentarioDerivacionNuevo"
                                        maxlength="500"
                                        style="height: 85px"
                                    ></textarea>
                                    <label for="comentarioDerivacionNuevo">Mensaje de Derivación</label>
                                    @error('comentarioDerivacionNuevo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Separador --}}
                        <div class="separator my-5"></div>

                        {{-- Sección de Archivos --}}
                        <div class="fw-bold text-dark mb-4 d-flex align-items-center">
                            <i class="ki-outline ki-folder fs-3 me-2"></i>
                            <span>Documentos adjuntos</span>
                        </div>

                        <div class="mb-4">
                            <label for="archivosDocumento" class="form-label fw-semibold">
                                Adjuntar documentos <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control @error('archivosDocumento') is-invalid @enderror @error('archivosDocumento.*') is-invalid @enderror" id="archivosDocumento" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" multiple wire:model="archivosDocumento" />
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                PDF, PNG, JPEG | Máximo 10MB por archivo | Máximo 10 archivos
                            </small>

                            @error('archivosDocumento')
                                <div class="alert alert-danger fs-7 mb-3 mt-2">
                                    <i class="bi bi-exclamation-triangle me-2"></i> {{ $message }}
                                </div>
                            @enderror
                            @error('archivosDocumento.*')
                                <div class="alert alert-danger fs-7 mb-3 mt-2">
                                    <i class="bi bi-exclamation-triangle me-2"></i> {{ $message }}
                                </div>
                            @enderror

                            <div wire:loading wire:target="archivosDocumento" class="mt-3 text-primary">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Cargando archivos...
                            </div>
                        </div>

                        {{-- Archivos a subir --}}
                        @if(!empty($archivosDocumento))
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="fw-semibold text-gray-700">
                                    <i class="bi bi-file-earmark-arrow-up me-2 text-primary"></i>
                                    Archivos a subir ({{ count($archivosDocumento) }}/10)
                                </span>
                            </div>
                            <div class="row g-3">
                                @foreach($archivosDocumento as $index => $archivo)
                                @php
                                    $nombre = $archivo->getClientOriginalName();
                                    $sizeBytes = $archivo->getSize();
                                    $sizeMB = number_format($sizeBytes / (1024 * 1024), 2);
                                    $ext = strtolower($archivo->getClientOriginalExtension());
                                    $isPdf = $ext === 'pdf';
                                    $isValid = $sizeBytes <= 10485760; // 10MB
                                @endphp
                                <div class="col-md-6 col-xl-4" wire:key="nuevo-arquivo-{{ $index }}">
                                    <div class="card border {{ $isValid ? 'border-gray-300' : 'border-danger' }} h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start">
                                                <div class="symbol symbol-45px me-3 flex-shrink-0">
                                                    <span class="symbol-label {{ $isValid ? 'bg-light-primary' : 'bg-light-danger' }}">
                                                        <i class="bi {{ $isPdf ? 'bi-file-earmark-pdf text-danger' : 'bi-image text-primary' }} fs-3"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden me-2">
                                                    <div class="fw-bold text-gray-800 text-truncate fs-7" title="{{ $nombre }}">
                                                        {{ Str::limit($nombre, 28) }}
                                                    </div>
                                                    <div class="text-muted fs-8 {{ $isValid ? '' : 'text-danger fw-semibold' }}">
                                                        {{ $sizeMB }} MB
                                                    </div>
                                                    @if (!$isValid)
                                                        <div class="text-danger fs-8 fw-semibold">⚠️ Muy grande</div>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-sm btn-icon btn-light-danger flex-shrink-0" wire:click="eliminarArchivo({{ $index }})" title="Quitar archivo">
                                                    <i class="ki-outline ki-trash fs-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Archivos existentes --}}
                        @if($modoModal == 2 && !empty($archivosExistentes) && count($archivosExistentes) > 0)
                        <div class="mb-4">
                            <div class="separator my-4"></div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="fw-semibold text-gray-700">
                                    <i class="bi bi-file-earmark-check text-success me-2"></i>
                                    Archivos guardados ({{ count($archivosExistentes) }})
                                </span>
                            </div>
                            <div class="row g-3">
                                @foreach($archivosExistentes as $archivoExistente)
                                <div class="col-md-6 col-xl-4" wire:key="archivo-{{ $archivoExistente->id_archivo_documento }}">
                                    <div class="card border border-success h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start">
                                                <div class="symbol symbol-45px me-3 flex-shrink-0">
                                                    <span class="symbol-label bg-light-{{ $archivoExistente->color }}">
                                                        <i class="bi {{ $archivoExistente->icono }} fs-3 text-{{ $archivoExistente->color }}"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="fw-bold text-gray-800 text-truncate fs-7" title="{{ $archivoExistente->nombre_original }}">
                                                        {{ Str::limit($archivoExistente->nombre_original, 22) }}
                                                    </div>
                                                    <div class="text-muted fs-8">
                                                        <i class="bi bi-check-circle-fill text-success"></i> {{ $archivoExistente->tamanio_formateado }}
                                                    </div>
                                                    <div class="d-flex gap-1 mt-2">
                                                        <a href="{{ route('archivo.ver', ['path' => $archivoExistente->ruta_archivo]) }}" target="_blank" class="btn btn-sm btn-light-success btn-xs">
                                                            <i class="ki-outline ki-eye fs-6"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-light-danger btn-xs" wire:click="eliminarArchivoExistente({{ $archivoExistente->id_archivo_documento }})" wire:confirm="¿Estás seguro de eliminar este archivo?">
                                                            <i class="ki-outline ki-trash fs-6"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                <div class="modal-footer flex-center border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="guardarDocumento">
                            <i class="ki-outline ki-check-circle fs-3"></i>
                            Guardar
                        </span>
                        <span wire:loading wire:target="guardarDocumento">
                            Procesando...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@script
<script>
    const select2 = (campo, parametro, modal, multiple = false, esBuscador = false) => {
        var accion = @this.accion; // Modo de acción del formulario

        // Verificar si es Registrar o Modificar
        if (accion === 'crear') {
            $('.' + campo).val(null).trigger('change');
        } else {
            const value = $('.' + campo).val();
            const esRequerido = $('.' + campo).siblings().hasClass('required');

            if ((!value || (Array.isArray(value) && value.length === 0)) && esRequerido) {
                $('.' + campo).addClass('is-invalid').removeClass('is-valid');
            } else if (!value || (Array.isArray(value) && value.length === 0)) {
                $('.' + campo).removeClass('is-valid');
            } else {
                $('.' + campo).removeClass('is-invalid').addClass('is-valid');
            }
        }

        let placeholderText = esBuscador ? 'Abre este buscador' : 'Abre esta selección';

        const opts = {
            placeholder: placeholderText,
            minimumResultsForSearch: parametro,
            allowClear: true,
            dropdownParent: $(`#${modal}`).length ? $(`#${modal}`) : $(document.body),
            language: {
                errorLoading: function() { return 'No se pudieron encontrar los resultados'; },
                loadingMore: function() { return 'Cargando más recursos...'; },
                noResults: function() { return 'No hay resultado'; },
                searching: function() { return 'Buscando...'; }
            }
        };

        // Lógica específica para buscar oficinas por abreviatura
        if (campo === 'oficina') {
            opts.matcher = function(params, data) {
                if ($.trim(params.term) === '') { return data; }
                const term = params.term.toLowerCase();
                const text = (data.text || '').toLowerCase();
                const abbr = ($(data.element).data('abreviatura') || '').toLowerCase();
                if (text.indexOf(term) > -1 || abbr.indexOf(term) > -1) { return data; }
                return null;
            };
        }

        $(`.${campo}`)
            .prop('multiple', multiple)
            .select2(opts)
            .on('change', function() {
                const value = $(this).val();
                const esRequerido = $(this).siblings().hasClass('required');

                @this.set(campo, value);

                setTimeout(() => {
                    $(this).next('.select2-container').removeClass('select2-container--focus');
                }, 50);

                if ((!value || (Array.isArray(value) && value.length === 0)) && esRequerido) {
                    $(`.${campo}`).addClass('is-invalid').removeClass('is-valid');
                } else if (!value || (Array.isArray(value) && value.length === 0)) {
                    $(`.${campo}`).removeClass('is-valid');
                } else {
                    $(`.${campo}`).removeClass('is-invalid').addClass('is-valid');
                }
            })
            .on('select2:open', function() {
                $('.select2-results__options').addClass('custom-select2-results');
            })
            .each(function() {
                $(this).next().find('.select2-selection--single').addClass(`form-select ${campo}`);
            });
    }

    // Inicialización de Select2 al cargar el componente Livewire
    document.addEventListener('livewire:initialized', () => {
        select2('tipoDocumentoCatalogo', 6, 'modal-documento');
        select2('idAreaDestino', 6, 'modal-documento');
        select2('oficina', 6, 'modal-documento');
    });

    // Reinicialización al actualizarse Livewire
    document.addEventListener("livewire:updated", () => {
        select2('tipoDocumentoCatalogo', 6, 'modal-documento');
        select2('idAreaDestino', 6, 'modal-documento');
        select2('oficina', 6, 'modal-documento');
    });

    // Inicialización cuando el modal de Bootstrap se muestra
    $('#modal-documento').on('shown.bs.modal', function() {
        setTimeout(() => {
            select2('tipoDocumentoCatalogo', 6, 'modal-documento');
            select2('idAreaDestino', 6, 'modal-documento');
            select2('oficina', 6, 'modal-documento');
        }, 150);
    });

    // Limpieza de Select2 al ocultar el modal
    $('#modal-documento').on('hidden.bs.modal', function() {
        const selects = ['tipoDocumentoCatalogo', 'idAreaDestino', 'oficina'];
        selects.forEach(clase => {
            if ($('.' + clase).hasClass('select2-hidden-accessible')) {
                $('.' + clase).select2('destroy');
            }
        });
    });

    // Listener para mostrar errores de validación desde el backend
    window.addEventListener('errores_validacion', (e) => {
        Object.keys(e.detail.validacion).forEach(function(clave) {
            $(`.${clave}`).addClass('is-invalid');
        });
    });

    // Listener para preseleccionar área (ej: al responder un documento)
    window.addEventListener('preseleccionar_area_destino', (e) => {
        setTimeout(() => {
            $('.idAreaDestino').val(e.detail.valor).trigger('change');
        }, 200);
    });

</script>
@endscript
