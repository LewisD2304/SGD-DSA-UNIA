<div wire:ignore.self class="modal fade" id="modal-detalle-documento" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">
                    Detalle del documento
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
                        <i class="ki-outline ki-document me-2"></i> Información del documento
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="fw-bold text-gray-600 mb-1">Número documento:</div>
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

                    @if($modeloDocumento->fecha_recepcion_documento)
                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Fecha recepción:</div>
                        <div class="text-gray-800">{{ \Carbon\Carbon::parse($modeloDocumento->fecha_recepcion_documento)->format('d/m/Y') }}</div>
                    </div>
                    @endif

                    @if($modeloDocumento->ruta_documento)
                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Archivo adjunto:</div>
                        <div>
                            <a
                                href="{{ route('archivo.descargar', [
                                    'ruta' => $modeloDocumento->ruta_documento,
                                    'nombre' => $modeloDocumento->nombre_archivo_original ?? 'documento.pdf'
                                ]) }}"
                                target="_blank"
                                class="btn btn-sm btn-light-primary"
                            >
                                <i class="ki-outline ki-file fs-3 me-1"></i>
                                {{ $modeloDocumento->nombre_archivo_original ?? 'Ver documento' }}
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <div class="fw-bold text-gray-600 mb-1">Estado:</div>
                        <div>
                            @if($modeloDocumento->estado)
                                <span class="badge badge-light-{{ $modeloDocumento->id_estado == 1 ? 'success' : 'danger' }} py-2 px-3">
                                    {{ $modeloDocumento->estado->nombre_estado }}
                                </span>
                            @else
                                <span class="badge badge-light-secondary py-2 px-3">Sin estado</span>
                            @endif
                        </div>
                    </div>

                    @endif

                </div>
            </div>

            <div class="modal-footer flex-center border-0">
                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
