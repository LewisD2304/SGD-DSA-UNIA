<div wire:ignore.self class="modal fade" id="modal-rectificar-documento" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">
                    Rectificar documento
                </h3>

                <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarRectificar">

                <div class="modal-body px-5">
                    <div class="d-flex flex-column px-5 px-lg-10">

                        <div class="fw-bold text-dark mb-3 mt-3">
                            <i class="ki-outline ki-document me-2"></i> Información del documento
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control bg-light" id="numeroDocumento" readonly value="{{ $numeroDocumento }}" />
                                    <label for="numeroDocumento">Número documento</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control bg-light" id="folioDocumento" readonly value="{{ $folioDocumento }}" />
                                    <label for="folioDocumento">Folio</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-floating">
                                <textarea class="form-control bg-light" id="asuntoDocumento" readonly style="height: 80px">{{ $asuntoDocumento }}</textarea>
                                <label for="asuntoDocumento">Asunto</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-light" id="idAreaDestino" readonly value="{{ $modeloDocumento?->areaDestino->nombre_area ?? 'Sin área' }}" />
                                <label for="idAreaDestino">Área actual</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="required fw-semibold fs-6 mb-2">Observacion para rectificación</label>
                            <textarea class="form-control form-control-solid text-uppercase @if ($errors->has('observacionesDerivar')) is-invalid @endif" id="observacionesDerivar" placeholder="Ingrese el motivo de la rectificación" wire:model="observacionesDerivar" maxlength="500" rows="4"></textarea>
                            <div class="text-muted fs-8 mt-1">{{ strlen($observacionesDerivar) }}/500 caracteres</div>
                            @error('observacionesDerivar')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="fw-semibold fs-6 mb-2">Archivos de evidencia (opcional)</label>
                            <input id="evidenciaInput" type="file" class="d-none @error('archivosEvidenciaRectificacion') is-invalid @enderror @error('archivosEvidenciaRectificacion.*') is-invalid @enderror" multiple accept=".pdf,.png,.jpg,.jpeg" wire:model="archivosEvidenciaRectificacion">

                            <button type="button" class="btn btn-light-primary" onclick="document.getElementById('evidenciaInput').click()">
                                <i class="bi bi-upload me-2"></i> Elegir archivos
                            </button>
                            <div class="text-muted fs-8 mt-2">Puedes adjuntar archivos PDF o imágenes (máx. 10MB cada uno)</div>

                            @error('archivosEvidenciaRectificacion')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                            @error('archivosEvidenciaRectificacion.*')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror

                            @if($archivosEvidenciaRectificacion && count($archivosEvidenciaRectificacion) > 0)
                            <div class="mt-3">
                                <div class="fw-semibold text-gray-700 mb-2">
                                    <i class="bi bi-paperclip me-1"></i> Archivos a subir ({{ count($archivosEvidenciaRectificacion) }})
                                </div>
                                <div class="row g-3">
                                    @foreach($archivosEvidenciaRectificacion as $idx => $archivo)
                                    @php
                                        $nombre = $archivo->getClientOriginalName();
                                        $sizeKB = number_format($archivo->getSize() / 1024, 0);
                                        $ext = strtolower($archivo->getClientOriginalExtension());
                                        $isPdf = $ext === 'pdf';
                                    @endphp
                                    <div class="col-md-6 col-lg-4" wire:key="file-{{ $idx }}">
                                        <div class="card border border-gray-300 h-100">
                                            <div class="card-body p-3 d-flex flex-column">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="me-3">
                                                        <i class="bi {{ $isPdf ? 'bi-file-earmark-pdf text-danger' : 'bi-image text-primary' }} fs-2x"></i>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <div class="fw-bold text-gray-800 text-truncate" title="{{ $nombre }}">{{ Str::limit($nombre, 30) }}</div>
                                                        <div class="text-muted fs-7">{{ $sizeKB }} KB</div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-light-danger w-100 mt-auto" wire:click="quitarArchivoEvidencia({{ $idx }})">
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
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="guardarRectificar">
                            <i class="ki-outline ki-check-circle fs-3 me-1"></i>
                            Rectificar
                        </span>
                        <span wire:loading wire:target="guardarRectificar">
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            Procesando...
                        </span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

