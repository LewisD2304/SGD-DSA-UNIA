<div wire:ignore.self class="modal fade" id="modal-accion-documento" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">
                    {{ $tituloModalDerivar }}
                </h3>

                <div
                    class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body px-5">
                <div class="d-flex flex-column px-5 px-lg-10">

                    @if ($modeloDocumento)

                    <!-- INFORMACIÓN DEL DOCUMENTO -->
                    <div class="fw-bold text-dark mb-3 mt-3">
                        <i class="ki-outline ki-document me-2"></i> Documento
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="fw-bold text-gray-600 mb-1">Número:</div>
                            <div class="text-gray-800">{{ $modeloDocumento->numero_documento }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="fw-bold text-gray-600 mb-1">Folio:</div>
                            <div class="text-gray-800">{{ $modeloDocumento->folio_documento }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Asunto:</div>
                        <div class="text-gray-800">{{ $modeloDocumento->asunto_documento }}</div>
                    </div>

                    <!-- FORMULARIO DE ACCIÓN -->
                    <div class="separator my-5"></div>

                    <form wire:submit.prevent="ejecutarAccion">

                        <!-- ÁREA DESTINO: Solo mostrar si es derivar o devolver -->
                        @if(in_array($accionActual, ['derivar', 'devolver']))
                        <div class="fv-row mb-5">
                            <label class="required fw-semibold fs-6 mb-2">Área de destino</label>
                            <select
                                wire:model="idAreaDerivar"
                                id="idAreaDerivar"
                                class="form-select form-select-solid"
                                data-placeholder="Seleccione un área"
                                data-allow-clear="true"
                            >
                                <option value=""></option>
                            </select>
                            <div>
                                @error('idAreaDerivar')
                                    <span class="text-danger fs-7">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @endif

                        <!-- OBSERVACIONES: Solo mostrar si es devolver o subsanar -->
                        @if(in_array($accionActual, ['devolver', 'subsanar']))
                        <div class="fv-row mb-5">
                            <label class="required fw-semibold fs-6 mb-2">Observaciones</label>
                            <textarea
                                wire:model="observacionesDerivar"
                                class="form-control form-control-solid"
                                rows="4"
                                placeholder="Ingrese las observaciones"
                                maxlength="500"
                            ></textarea>
                            <div class="text-muted fs-8 mt-1">
                                {{ strlen($observacionesDerivar) }}/500 caracteres
                            </div>
                            <div>
                                @error('observacionesDerivar')
                                    <span class="text-danger fs-7">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @endif

                        <!-- BOTONES -->
                        <div class="text-center pt-5">
                            <button
                                type="button"
                                class="btn btn-light me-3"
                                data-bs-dismiss="modal"
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <span class="indicator-label">
                                    Confirmar
                                </span>
                            </button>
                        </div>
                    </form>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $('#modal-accion-documento').on('shown.bs.modal', function() {
        setTimeout(function() {
            // Inicializar Select2 para área destino si existe
            if ($('#idAreaDerivar').length > 0) {
                select2('idAreaDerivar', 4, 'modal-accion-documento');
            }
        }, 150);
    });

    $('#modal-accion-documento').on('hidden.bs.modal', function() {
        // Destruir Select2 al cerrar el modal
        if ($('#idAreaDerivar').length > 0 && $('#idAreaDerivar').hasClass('select2-hidden-accessible')) {
            $('#idAreaDerivar').select2('destroy');
        }
    });
</script>
@endscript
