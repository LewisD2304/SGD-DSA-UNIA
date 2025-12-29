@php
use Illuminate\Support\Facades\Storage;
@endphp

<div wire:ignore.self class="modal fade" id="modal-documento" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-900px">
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

                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-10">

                        <!-- INFORMACIÓN DEL DOCUMENTO -->
                        <div class="fw-bold text-dark mb-3 mt-3">
                            <i class="ki-outline ki-document me-2"></i> Información del documento
                        </div>

                        <!-- Número documento y Folio -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control text-uppercase @if ($errors->has('numeroDocumento')) is-invalid @elseif($numeroDocumento) is-valid @endif" id="numeroDocumento" autocomplete="off" placeholder="Ej: CARTA MULTIPLE Nº 004-2025-UNIA-VRA/DSA" wire:model.live="numeroDocumento" maxlength="100" />
                                    <label for="numeroDocumento">
                                        Número documento <span class="text-danger">*</span>
                                    </label>
                                    @error('numeroDocumento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" class="form-control @if ($errors->has('folioDocumento')) is-invalid @elseif($folioDocumento) is-valid @endif" id="folioDocumento" autocomplete="off" placeholder="Folio" wire:model.live="folioDocumento" min="1" step="1" @keydown.minus.prevent />

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

                        <!-- Asunto -->
                        <div class="mb-3">
                            <div class="form-floating">
                                <textarea class="form-control text-uppercase @if ($errors->has('asuntoDocumento')) is-invalid @elseif($asuntoDocumento) is-valid @endif" id="asuntoDocumento" autocomplete="off" placeholder="Asunto" wire:model.live="asuntoDocumento" maxlength="200" style="height: 100px" @keypress="if (!/[A-Za-z0-9\s,.]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') event.preventDefault()"></textarea>
                                <label for="asuntoDocumento">
                                    Asunto <span class="text-danger">*</span>
                                </label>
                                @error('asuntoDocumento')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Observación -->
                        <div class="mb-3">
                            <div class="form-floating">
                                <textarea class="form-control text-uppercase @error('observacionDocumento') is-invalid @enderror" id="observacionDocumento" autocomplete="off" placeholder="Observación" wire:model="observacionDocumento" maxlength="500" style="height: 80px"></textarea>
                                <label for="observacionDocumento">
                                    Observación (Opcional)
                                </label>
                                @error('observacionDocumento')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tipo de Documento -->
                        <div class="mb-3">
                            <label for="tipoDocumentoCatalogo" class="form-label required">
                                Tipo de Documento <span class="text-danger"></span>
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

                        <!-- Área remitente (solo en edición) -->
                        @if ($modoModal === 2)
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-light" id="areaRemitente" readonly value="{{ auth()->user()->persona->area->nombre_area ?? 'Sin área asignada' }}" />
                                <label for="areaRemitente">
                                    Remitente (Área)
                                </label>
                            </div>
                        </div>
                        @endif

                        <!-- Destino (select de áreas) -->
                        <div class="mb-3">
                            <label for="idAreaDestino" class="form-label required">
                                Destino (Área) <span class="text-danger"></span>
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

                        <!-- Adjuntar documentos -->
                        <div class="mb-3">
                            <label for="archivosDocumento" class="form-label">
                                Adjuntar documentos (PDF, JPG, PNG - máx. 10MB c/u) <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control @error('archivosDocumento.*') is-invalid @enderror"
                                   id="archivosDocumento"
                                   accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg"
                                   multiple
                                   wire:model="archivosDocumento" />
                            @error('archivosDocumento.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <!-- Archivos en cola para subir -->
                            @if(!empty($archivosDocumento))
                            <div class="mt-4">
                                <div class="fw-bold text-dark mb-3">
                                    <i class="ki-outline ki-file-up fs-3 me-2"></i> Archivos a subir ({{ count($archivosDocumento) }})
                                </div>
                                <div class="row g-3">
                                    @foreach($archivosDocumento as $index => $archivo)
                                    <div class="col-md-6 col-lg-4" wire:key="nuevo-archivo-{{ $index }}">
                                        <div class="card shadow-sm border border-gray-300 h-100">
                                            <div class="card-body p-4 d-flex flex-column">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="symbol symbol-50px me-3">
                                                        <span class="symbol-label bg-light-primary">
                                                            <i class="ki-outline ki-file-sheet fs-2x text-primary"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <div class="fw-bold text-gray-800 text-truncate" title="{{ $archivo->getClientOriginalName() }}">
                                                            {{ Str::limit($archivo->getClientOriginalName(), 20) }}
                                                        </div>
                                                        <div class="text-muted fs-7">
                                                            {{ number_format($archivo->getSize() / 1024, 0) }} KB
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2 mt-auto">
                                                    <button type="button" class="btn btn-sm btn-light-danger flex-fill" wire:click="eliminarArchivo({{ $index }})">
                                                        <i class="ki-outline ki-trash fs-5"></i> Quitar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Archivos ya guardados (modo edición) -->
                            @if($modoModal == 2 && !empty($archivosExistentes) && count($archivosExistentes) > 0)
                            <div class="mt-4">
                                <div class="separator my-4"></div>
                                <div class="fw-bold text-dark mb-3">
                                    <i class="ki-outline ki-file-check fs-3 me-2 text-success"></i> Archivos guardados ({{ count($archivosExistentes) }})
                                </div>
                                <div class="row g-3">
                                    @foreach($archivosExistentes as $archivoExistente)
                                    <div class="col-md-6 col-lg-4" wire:key="archivo-{{ $archivoExistente->id_archivo_documento }}">
                                        <div class="card shadow-sm border border-success border-2 h-100">
                                            <div class="card-body p-4 d-flex flex-column">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="symbol symbol-50px me-3">
                                                        <span class="symbol-label bg-light-{{ $archivoExistente->color }}">
                                                            <i class="ki-outline {{ $archivoExistente->icono }} fs-2x text-{{ $archivoExistente->color }}"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <div class="fw-bold text-gray-800 text-truncate" title="{{ $archivoExistente->nombre_original }}">
                                                            {{ Str::limit($archivoExistente->nombre_original, 20) }}
                                                        </div>
                                                        <div class="text-muted fs-7">
                                                            {{ $archivoExistente->tamanio_formateado }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2 mt-auto">
                                                    <a href="{{ route('archivo.ver', ['path' => $archivoExistente->ruta_archivo]) }}" target="_blank" class="btn btn-sm btn-light-success flex-fill">
                                                        <i class="ki-outline ki-eye fs-5"></i> Ver
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-light-danger" wire:click="eliminarArchivoExistente({{ $archivoExistente->id_archivo_documento }})" wire:confirm="¿Estás seguro de eliminar este archivo?">
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

                            <div wire:loading wire:target="archivosDocumento" class="mt-3 text-primary">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Cargando archivos...
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer flex-center border-0">
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

        $(`.${campo}`)
            .prop('multiple', multiple)
            .select2({
                placeholder: placeholderText
                , minimumResultsForSearch: parametro
                , allowClear: true
                , dropdownParent: $(`#${modal}`).length ? $(`#${modal}`) : $(document.body)
                , language: {
                    errorLoading: function() {
                        return 'No se pudieron encontrar los resultados';
                    }
                    , loadingMore: function() {
                        return 'Cargando más recursos...';
                    }
                    , noResults: function() {
                        return "No hay resultado";
                    }
                    , searching: function() {
                        return "Buscando...";
                    }
                }
            }).on('change', function() {
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

    const select2Paginado = (campo) => {
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

        $(`.${campo}`).select2({
                placeholder: "Buscar ubigeo"
                , minimumInputLength: 2
                , dropdownParent: $(document.body)
                , allowClear: true
                , ajax: {
                    url: '/api/buscar-ubigeo'
                    , dataType: 'json'
                    , delay: 250
                    , headers: {
                        'X-API-KEY': '{{ config('
                        services.keys.api_key.key ') }}'
                    }
                    , data: function(params) {
                        return {
                            searchTerm: params.term // El término de búsqueda desde el select2
                        };
                    }
                    , processResults: function(data) {
                        return {
                            results: data.map(item => {
                                return {
                                    id: item.codigo_ubigeo
                                    , text: item.codigo_ubigeo + ' - ' + item.distrito_ubigeo +
                                        ' - ' + item.provincia_ubigeo + ' - ' + item
                                        .departamento_ubigeo
                                };
                            })
                        };
                    }
                    , cache: true
                }
                , language: {
                    errorLoading: function() {
                        return 'No se pudieron encontrar los resultados';
                    }
                    , loadingMore: function() {
                        return 'Cargando más recursos...';
                    }
                    , noResults: function() {
                        return "No hay resultado";
                    }
                    , searching: function() {
                        return "Buscando...";
                    }
                    , inputTooShort: function() {
                        return "Ingrese mínimo 2 caracteres";
                    }
                }
            }).on('change', function() {
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
            .on('select2:clear', function() {
                @this.set(campo, ''); // Aquí limpiamos la variable en Livewire
            })
            .on('select2:open', function() {
                $('.select2-results__options').addClass('custom-select2-results');
            })
            .each(function() {
                $(this).next().find('.select2-selection--single').addClass(`form-select ${campo}`);
            });
    };

    document.addEventListener('livewire:initialized', () => {
        select2('tipoDocumentoCatalogo', 6, 'modal-documento');
        select2('idAreaDestino', 6, 'modal-documento');
    });

    document.addEventListener("livewire:updated", () => {
        select2('tipoDocumentoCatalogo', 6, 'modal-documento');
        select2('idAreaDestino', 6, 'modal-documento');
    });

    $('#modal-documento').on('shown.bs.modal', function() {
        setTimeout(() => {
            select2('tipoDocumentoCatalogo', 6, 'modal-documento');
            select2('idAreaDestino', 6, 'modal-documento');
        }, 150);
    });

    $('#modal-documento').on('hidden.bs.modal', function() {
        if ($('.tipoDocumentoCatalogo').hasClass('select2-hidden-accessible')) {
            $('.tipoDocumentoCatalogo').select2('destroy');
        }
        if ($('.idAreaDestino').hasClass('select2-hidden-accessible')) {
            $('.idAreaDestino').select2('destroy');
        }
    });

    window.addEventListener('errores_validacion', (e) => {
        Object.keys(e.detail.validacion).forEach(function(clave) {
            $(`.${clave}`).addClass('is-invalid');
        });
    });

    window.addEventListener('autocompletado_select_paginado', (e) => {
        const campos = Array.isArray(e.detail) ? e.detail[0] : e.detail;
        if (!campos || typeof campos !== 'object') return;

        for (const clave in campos) {
            if (!campos.hasOwnProperty(clave)) continue;

            const {
                id
                , text
            } = campos[clave];
            if (!id || !text) continue;

            const select2 = $('.' + clave);
            if (select2.length === 0) continue;

            select2.empty();

            const option = new Option(text, id, true, true);
            select2.append(option).trigger('change');

            if (typeof select2Paginado === 'function') {
                select2Paginado(clave);
            }
        }
    });

</script>

@endscript
