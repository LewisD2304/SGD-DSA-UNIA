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
                                    <option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('idAreaObservar') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="required fw-semibold fs-6 mb-2">Motivo de la observación</label>
                            <textarea wire:model.live="motivoObservacion" class="form-control form-control-solid @error('motivoObservacion') is-invalid @enderror" rows="4" placeholder="Indique detalladamente por qué se observa el documento..." maxlength="500"></textarea>

                            <div class="text-muted fs-8 mt-1 text-end">
                                {{ strlen($motivoObservacion ?? '') }}/500 caracteres
                            </div>
                            @error('motivoObservacion') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="fw-semibold fs-6 mb-2">Adjuntar evidencia (Opcional)</label>

                            <div class="d-flex gap-2 align-items-center mb-3">
                                <label class="btn btn-sm btn-light-primary">
                                    <i class="ki-outline ki-file-up fs-3"></i> Seleccionar archivos
                                    <input type="file" wire:model="archivosEvidenciaObservacion" class="d-none" multiple accept=".pdf,.jpg,.jpeg,.png">
                                </label>
                                <span class="text-gray-500 fs-8">
                                    Formatos: PDF, PNG, JPEG | Máx 10MB c/u | Máx 10 archivos
                                </span>
                            </div>

                            @error('archivosEvidenciaObservacion')
                            <div class="alert alert-danger fs-7 mb-2">
                                <i class="bi bi-exclamation-triangle me-2"></i> {{ $message }}
                            </div>
                            @enderror
                            @error('archivosEvidenciaObservacion.*')
                            <div class="alert alert-danger fs-7 mb-2">
                                <i class="bi bi-exclamation-triangle me-2"></i> {{ $message }}
                            </div>
                            @enderror

                            @if(is_array($archivosEvidenciaObservacion) && count($archivosEvidenciaObservacion) > 0)
                            <div>
                                <div class="fw-semibold text-gray-700 mb-2">
                                    <i class="bi bi-paperclip me-1"></i> Archivos a subir ({{ count($archivosEvidenciaObservacion) }}/10)
                                </div>
                                <div class="row g-2">
                                    @foreach($archivosEvidenciaObservacion as $index => $archivo)
                                    @php
                                        $nombre = method_exists($archivo, 'getClientOriginalName') ? $archivo->getClientOriginalName() : 'Archivo';
                                        $size = method_exists($archivo, 'getSize') ? $archivo->getSize() : 0;
                                        $sizeKB = number_format($size / 1024, 2);
                                        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                                        $isPdf = $ext === 'pdf';
                                        $isValid = $size <= 10485760;
                                    @endphp
                                    <div class="col-md-6" wire:key="obs-file-{{ $index }}">
                                        <div class="card border {{ $isValid ? 'border-gray-300' : 'border-danger' }} h-100">
                                            <div class="card-body p-3 d-flex flex-column">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="me-2">
                                                        <i class="bi {{ $isPdf ? 'bi-file-earmark-pdf text-danger' : 'bi-image text-primary' }} fs-2"></i>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <div class="fw-bold text-gray-800 text-truncate text-sm" title="{{ $nombre }}">{{ Str::limit($nombre, 25) }}</div>
                                                        <div class="text-muted fs-7 {{ $isValid ? '' : 'text-danger' }}">{{ $sizeKB }} KB</div>
                                                        @if (!$isValid)
                                                        <div class="text-danger fs-7 fw-semibold">Archivo muy grande</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-light-danger w-100 mt-auto" wire:click="quitarArchivoObservacion({{ $index }})">
                                                    <i class="bi bi-trash me-1"></i> Quitar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                    </div>
                </div>

                <div class="modal-footer flex-center border-0">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" wire:loading.attr="disabled">
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
            }).on('change', function(e) {
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
