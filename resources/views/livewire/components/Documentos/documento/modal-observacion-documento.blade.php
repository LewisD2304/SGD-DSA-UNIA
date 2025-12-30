<div wire:ignore.self class="modal fade" id="modal-observacion-documento" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0 text-warning">
                    <i class="ki-outline ki-eye-slash fs-2 me-2 text-warning"></i> Observar Documento
                </h3>
                <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <form autocomplete="off" wire:submit.prevent="guardarObservacion">
                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-10">

                        <div class="alert alert-dismissible bg-light-warning d-flex flex-column flex-sm-row p-5 mb-5">
                            <div class="d-flex flex-column pe-0 pe-sm-10">
                                <h5 class="mb-1">Documento: {{ $numeroDocumento }}</h5>
                                <span>{{ Str::limit($asuntoDocumento, 100) }}</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="required fw-semibold fs-6 mb-2">Devolver a (Área destino)</label>
                            <div wire:ignore>
                                <select class="form-select form-select-solid" id="select_area_observar" data-control="select2" data-placeholder="Seleccione un área">
                                    <option></option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}" @selected($idAreaObservar == $area->id_area)>{{ $area->nombre_area }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('idAreaObservar') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="required fw-semibold fs-6 mb-2">Motivo de la observación</label>
                            <textarea
                                wire:model.live="motivoObservacion"
                                class="form-control form-control-solid @error('motivoObservacion') is-invalid @enderror"
                                rows="4"
                                placeholder="Indique detalladamente por qué se observa el documento..."
                                maxlength="500"></textarea>

                            <div class="text-muted fs-8 mt-1 text-end">
                                {{ strlen($motivoObservacion ?? '') }}/500 caracteres
                            </div>
                            @error('motivoObservacion') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="fw-semibold fs-6 mb-2">Adjuntar evidencia (Opcional)</label>

                            <div class="d-flex align-items-center mb-3">
                                <label class="btn btn-sm btn-light-primary me-3">
                                    <i class="ki-outline ki-file-up fs-3"></i> Seleccionar archivos
                                    <input type="file" wire:model="archivosEvidenciaObservacion" class="d-none" multiple accept=".pdf,.jpg,.jpeg,.png">
                                </label>
                                <span class="text-gray-500 fs-8">Máx 10MB (PDF, JPG, PNG)</span>
                            </div>

                            @error('archivosEvidenciaObservacion.*')
                                <div class="text-danger fs-7 mb-2">{{ $message }}</div>
                            @enderror

                            @if(is_array($archivosEvidenciaObservacion) && count($archivosEvidenciaObservacion) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($archivosEvidenciaObservacion as $index => $archivo)
                                        <div class="badge badge-light-secondary d-flex align-items-center p-2">
                                            <span class="me-2">
                                                {{ method_exists($archivo, 'getClientOriginalName') ? Str::limit($archivo->getClientOriginalName(), 20) : 'Archivo' }}
                                            </span>
                                            <i class="ki-outline ki-trash fs-5 text-danger cursor-pointer" wire:click="quitarArchivoObservacion({{ $index }})"></i>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <div class="modal-footer flex-center border-0">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <span wire:loading.remove wire:target="guardarObservacion">
                            <i class="ki-outline ki-eye-slash fs-2 me-1"></i> Observar Documento
                        </span>
                        <span wire:loading wire:target="guardarObservacion">
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span> Procesando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script para inicializar Select2 dentro del modal --}}
@script
<script>
    Livewire.on('inicializarSelect2Observacion', () => {
        setTimeout(() => {
            $('#select_area_observar').select2({
                dropdownParent: $('#modal-observacion-documento')
            }).on('change', function (e) {
                @this.set('idAreaObservar', $(this).val());
            });

            // Establecer valor inicial si existe
            const valorActual = @this.get('idAreaObservar');
            if (valorActual) {
                $('#select_area_observar').val(valorActual).trigger('change');
            } else {
                $('#select_area_observar').val(null).trigger('change');
            }

        }, 150);
    });
</script>
@endscript
