<div wire:ignore.self class="modal fade" id="modal-anular-documento" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">
                    Anular documento
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
                        <i class="ki-duotone ki-information-5 fs-5x text-danger mb-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>

                        <div class="fs-5 text-gray-800 fw-semibold">
                            ¿Está seguro de anular el documento
                            <span class="fw-bold">{{ $nombreDocumentoAnular }}</span>?
                        </div>

                        <div class="text-muted mt-3">
                            El documento pasará a estado ANULADO y se registrará en el historial.
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
                    class="btn btn-danger"
                    wire:click="anularDocumento"
                >
                    <span wire:loading.remove wire:target="anularDocumento">
                        <i class="ki-outline ki-cross-circle fs-3"></i>
                        Anular
                    </span>
                    <span wire:loading wire:target="anularDocumento">
                        Procesando...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
