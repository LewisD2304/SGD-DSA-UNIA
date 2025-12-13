<div wire:ignore.self class="modal fade" id="modal-estado-documento" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">
                    {{ $modoModal == 1 ? 'Habilitar' : 'Deshabilitar' }} documento
                </h3>

                <div
                    class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-column px-5">
                    <div class="text-center mb-5">
                        @if($modoModal == 1)
                            <i class="ki-duotone ki-information-5 fs-5x text-success mb-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        @else
                            <i class="ki-duotone ki-information-5 fs-5x text-danger mb-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        @endif

                        <div class="fs-5 text-gray-800 fw-semibold">
                            ¿Está seguro de {{ $modoModal == 1 ? 'habilitar' : 'deshabilitar' }} el documento
                            <span class="fw-bold">{{ $nombreDocumentoEstado }}</span>?
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer flex-center border-0">
                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn {{ $modoModal == 1 ? 'btn-success' : 'btn-danger' }}"
                    wire:click="cambiarEstadoDocumento"
                >
                    <span wire:loading.remove wire:target="cambiarEstadoDocumento">
                        <i class="ki-outline ki-check-circle fs-3"></i>
                        {{ $modoModal == 1 ? 'Habilitar' : 'Deshabilitar' }}
                    </span>
                    <span wire:loading wire:target="cambiarEstadoDocumento">
                        Procesando...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
