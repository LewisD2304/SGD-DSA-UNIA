<div>
    <div wire:ignore.self class="modal fade" id="modal-responder-documento" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header py-3 px-5">
                    <h3 class="fw-bold my-0 d-flex align-items-center">
                        <i class="ki-outline ki-message-edit fs-2 me-2 text-primary"></i>
                        <span>Responder documento</span>
                    </h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <form autocomplete="off" novalidate class="form" wire:submit="guardarRespuesta">
                    <div class="modal-body px-5 py-4">

                        @php
                            $nombreEstado = strtoupper($modeloDocumento->estado->nombre_estado ?? '');
                            $esObservacionRecepcionado = str_contains($nombreEstado, 'OBSERVACION') && str_contains($nombreEstado, 'RECEPCIONADO');
                            $archivosExistentesCollection = collect($archivosExistentes);
                            $archivosEvidenciaObservacion = $archivosExistentesCollection->where('tipo_archivo', 'evidencia_observacion');
                        @endphp

                        {{-- Alerta de Documento Observado --}}
                        @if($esObservacionRecepcionado && !empty($motivoObservacion))
                        <div class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex align-items-start p-4 mb-5">
                            <i class="ki-outline ki-information-5 fs-2hx text-danger me-3"></i>
                            <div class="d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="mb-0 fw-bold text-danger">Documento Observado</h5>
                                    <span class="badge badge-danger fs-8">{{ formatoFechaHoras($modeloDocumento->au_fechamo)}}</span>
                                </div>
                                <p class="text-gray-700 fs-7 mb-2">{{ $motivoObservacion }}</p>

                                @if($archivosEvidenciaObservacion->count() > 0)
                                <div class="mt-2 bg-white rounded p-3 border border-danger border-dashed">
                                    <span class="fs-8 fw-bold text-danger mb-2 d-block">
                                        <i class="ki-outline ki-file fs-6 me-1"></i> Evidencias adjuntas:
                                    </span>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($archivosEvidenciaObservacion as $archivo)
                                            <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank"
                                               class="badge badge-light-danger text-hover-danger fs-8 px-3 py-2">
                                                <i class="ki-outline ki-file fs-6 me-1"></i> {{ Str::limit($archivo->nombre_original, 25) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="row g-5">
                            {{-- Columna Izquierda: Detalles del Documento --}}
                            <div class="col-lg-5">

                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="ki-outline ki-document fs-3 text-primary me-2"></i>
                                        <h5 class="fw-bold text-gray-800 m-0">Detalles del Documento</h5>
                                    </div>
                                    <span class="text-muted fs-7">Información de referencia del expediente</span>
                                </div>

                                {{-- Tarjeta de información compacta --}}
                                <div class="card border border-gray-300 mb-4">
                                    <div class="card-body p-4">
                                        <div class="row g-3 mb-3">
                                            <div class="col-7">
                                                <span class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">N° Documento</span>
                                                <span class="fw-bold text-dark fs-6">{{ $numeroDocumento }}</span>
                                            </div>
                                            <div class="col-5">
                                                <span class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Folio</span>
                                                <span class="fw-bold text-dark fs-6">{{ $folioDocumento }}</span>
                                            </div>
                                        </div>
                                        <div class="separator separator-dashed my-3"></div>
                                        <div>
                                            <span class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Tipo de Documento</span>
                                            <span class="badge badge-light-primary fs-7 fw-bold px-3 py-2">{{ $tipoDocumentoNombre }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Remitente --}}
                                <div class="mb-4">
                                    <label class="fw-bold text-gray-700 fs-7 mb-2 d-flex align-items-center">
                                        <i class="ki-outline ki-profile-user fs-5 me-1"></i> Remitente / Oficina
                                    </label>
                                    <div class="p-3 bg-light rounded border border-gray-300">
                                        <div class="text-gray-800 fw-semibold fs-6 mb-1">{{ $areaRemitenteNombre }}</div>
                                        <div class="text-muted fs-7">{{ $oficinaNombre }}</div>
                                    </div>
                                </div>

                                {{-- Asunto --}}
                                <div class="mb-4">
                                    <label class="fw-bold text-gray-700 fs-7 mb-2 d-flex align-items-center">
                                        <i class="ki-outline ki-information fs-5 me-1"></i> Asunto
                                    </label>
                                    <div class="p-3 bg-light-info rounded border border-info border-dashed">
                                        <p class="text-gray-800 fs-7 mb-0">{{ $asuntoDocumento }}</p>
                                    </div>
                                </div>

                                {{-- Observación Original --}}
                                @if(!empty($observacionDocumento))
                                <div class="mb-4">
                                    <label class="fw-bold text-gray-700 fs-7 mb-2 d-flex align-items-center">
                                        <i class="ki-outline ki-message-text fs-5 me-1"></i> Observación Original
                                    </label>
                                    <div class="p-3 bg-light rounded border-start border-3 border-warning">
                                        <p class="text-gray-600 fs-7 fst-italic mb-0">"{{ $observacionDocumento }}"</p>
                                    </div>
                                </div>
                                @endif

                                {{-- Archivos Adjuntos --}}
                                @if(!empty($archivosExistentes) && count($archivosExistentes) > 0)
                                <div class="pt-3 border-top border-gray-300">
                                    <label class="fw-bold text-gray-800 fs-7 mb-3 d-flex align-items-center">
                                        <i class="ki-outline ki-paper-clip fs-5 me-1"></i>
                                        Archivos adjuntos ({{ count($archivosExistentes) }})
                                    </label>
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($archivosExistentes as $archivo)
                                        <div class="d-flex align-items-center justify-content-between p-2 border rounded bg-white hover-elevate-up">
                                            <div class="d-flex align-items-center overflow-hidden flex-grow-1 me-2">
                                                <div class="symbol symbol-35px me-2 flex-shrink-0">
                                                    <span class="symbol-label bg-light-primary">
                                                        <i class="ki-outline ki-file text-primary fs-4"></i>
                                                    </span>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <div class="text-gray-800 fw-semibold fs-8 text-truncate" title="{{ $archivo->nombre_archivo }}">
                                                        {{ Str::limit($archivo->nombre_archivo, 30) }}
                                                    </div>
                                                    <div class="text-muted fs-9">{{ number_format($archivo->tamano_archivo / 1024, 2) }} KB</div>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-1 flex-shrink-0">
                                                <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank"
                                                   class="btn btn-sm btn-icon btn-light-success w-30px h-30px" title="Ver archivo">
                                                    <i class="ki-outline ki-eye fs-5"></i>
                                                </a>
                                                <button type="button"
                                                        wire:click="eliminarArchivoExistente({{ $archivo->id_archivo_documento }})"
                                                        wire:confirm="¿Estás seguro de eliminar este archivo?"
                                                        class="btn btn-sm btn-icon btn-light-danger w-30px h-30px"
                                                        title="Eliminar">
                                                    <i class="ki-outline ki-trash fs-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Columna Derecha: Datos de Respuesta --}}
                            <div class="col-lg-7 border-start ps-lg-5">

                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-outline ki-send fs-3 text-success me-2"></i>
                                            <h5 class="fw-bold text-gray-800 m-0">Datos de Respuesta</h5>
                                        </div>
                                        @if($comentarioDerivacion)
                                            <span class="badge badge-light-primary fs-8 fw-semibold cursor-pointer"
                                                  data-bs-toggle="tooltip"
                                                  title="{{ $comentarioDerivacion }}">
                                                <i class="ki-outline ki-message-text fs-6 me-1"></i> Ver nota previa
                                            </span>
                                        @endif
                                    </div>
                                    <div class="separator separator-dashed border-gray-300 my-3"></div>
                                </div>

                                {{-- Enviar a --}}
                                <div class="mb-5">
                                    <label for="idAreaRespuesta" class="form-label required fw-bold fs-6 mb-2">
                                        <i class="ki-outline ki-share fs-5 me-1"></i> Enviar a
                                    </label>
                                    <div wire:ignore>
                                        <select class="form-select form-select-solid" id="idAreaRespuesta" wire:model="idAreaRespuesta" data-control="select2" data-placeholder="Seleccione área de destino">
                                            <option></option>
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('idAreaRespuesta')
                                        <div class="text-danger fs-7 mt-2">
                                            <i class="ki-outline ki-cross-circle fs-6 me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                {{-- Comentario / Respuesta --}}
                                <div class="mb-5">
                                    <label for="comentarioRespuesta" class="form-label fw-bold fs-6 mb-2">
                                        <i class="ki-outline ki-message-edit fs-5 me-1"></i> Comentario / Respuesta
                                    </label>
                                    <textarea
                                        class="form-control form-control-solid @error('comentarioRespuesta') is-invalid @enderror"
                                        id="comentarioRespuesta"
                                        wire:model="comentarioRespuesta"
                                        rows="4"
                                        placeholder="Escriba aquí los detalles de su respuesta..."
                                        style="resize: none;"
                                    ></textarea>
                                    @error('comentarioRespuesta')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Adjuntar sustento --}}
                                <div>
                                    <label class="form-label fw-bold fs-6 mb-2">
                                        <i class="ki-outline ki-file-up fs-5 me-1"></i> Adjuntar sustento
                                        <span class="text-muted fw-normal fs-7">(Opcional)</span>
                                    </label>

                                    <div class="position-relative">
                                        <div class="d-flex justify-content-center align-items-center border-2 border-dashed border-primary rounded bg-light-primary p-5 cursor-pointer hover-elevate-up">
                                            <div class="text-center">
                                                <i class="ki-outline ki-file-up fs-3x text-primary mb-2"></i>
                                                <div class="fs-6 fw-bold text-gray-900 mb-1">Arrastre archivos o haga clic aquí</div>
                                                <div class="fs-7 text-muted">PDF, PNG, JPG (Máx. 10MB c/u)</div>
                                            </div>
                                            <input type="file"
                                                   class="position-absolute w-100 h-100 top-0 start-0 opacity-0 cursor-pointer"
                                                   wire:model="archivosRespuesta"
                                                   multiple
                                                   accept=".pdf,.png,.jpg,.jpeg" />
                                        </div>
                                    </div>

                                    @error('archivosRespuesta')
                                        <div class="text-danger fs-7 mt-2 text-center">
                                            <i class="ki-outline ki-cross-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror

                                    {{-- Lista de archivos cargados --}}
                                    @if(!empty($archivosRespuesta) && count($archivosRespuesta) > 0)
                                    <div class="mt-4">
                                        <div class="fw-semibold text-gray-700 fs-7 mb-2">
                                            Archivos seleccionados ({{ count($archivosRespuesta) }})
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($archivosRespuesta as $index => $archivo)
                                            <div class="d-flex align-items-center justify-content-between p-2 bg-white rounded border border-success">
                                                <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                                                    <div class="symbol symbol-35px me-2 flex-shrink-0">
                                                        <span class="symbol-label bg-light-success">
                                                            <i class="ki-outline ki-file text-success fs-4"></i>
                                                        </span>
                                                    </div>
                                                    <div class="overflow-hidden">
                                                        <div class="fs-7 fw-semibold text-gray-800 text-truncate" title="{{ $archivo->getClientOriginalName() }}">
                                                            {{ Str::limit($archivo->getClientOriginalName(), 35) }}
                                                        </div>
                                                        <div class="fs-9 text-muted">{{ number_format($archivo->getSize() / 1024, 2) }} KB</div>
                                                    </div>
                                                </div>
                                                <button type="button"
                                                        wire:click="eliminarArchivoRespuesta({{ $index }})"
                                                        class="btn btn-icon btn-sm btn-light-danger flex-shrink-0 ms-2">
                                                    <i class="ki-outline ki-trash fs-5"></i>
                                                </button>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-3 px-5">
                        <button type="button" class="btn btn-light px-5" data-bs-dismiss="modal" wire:click="limpiarModalResponder">
                            <i class="ki-outline ki-cross fs-3 me-1"></i> Cancelar
                        </button>

                        @php
                            $textoBoton = $esObservacionRecepcionado ? 'Subsanar y Enviar' : 'Enviar Respuesta';
                            $claseBoton = $esObservacionRecepcionado ? 'btn-danger' : 'btn-primary';
                            $iconoBoton = $esObservacionRecepcionado ? 'ki-shield-tick' : 'ki-send';
                        @endphp

                        <button type="submit" class="btn {{ $claseBoton }} px-5" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="guardarRespuesta" class="d-flex align-items-center">
                                <i class="ki-outline {{ $iconoBoton }} fs-3 me-2"></i> {{ $textoBoton }}
                            </span>
                            <span wire:loading wire:target="guardarRespuesta">
                                <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @script
    <script>
        $(document).ready(function() {
            $('#idAreaRespuesta').select2({
                placeholder: 'Seleccione un área de destino',
                allowClear: true,
                dropdownParent: $('#modal-responder-documento'),
                width: '100%'
            });
            $('#idAreaRespuesta').on('change', function() {
                @this.set('idAreaRespuesta', $(this).val());
            });
        });

        window.addEventListener('abrir_modal_responder_documento', () => {
            $('#modal-responder-documento').modal('show');
            setTimeout(() => {
                $('#idAreaRespuesta').select2({
                    dropdownParent: $('#modal-responder-documento'),
                    width: '100%',
                    placeholder: 'Seleccione un área de destino',
                    allowClear: true
                });
            }, 300);
        });

        window.addEventListener('cerrar_modal_responder_documento', () => {
            $('#modal-responder-documento').modal('hide');
        });

        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
    @endscript
</div>
