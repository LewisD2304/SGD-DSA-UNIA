<div wire:ignore.self class="modal fade" id="modal-detalle-persona" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="fw-bold my-0">Detalle persona</h3>
                <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body px-5">
                <div class="d-flex flex-column px-5 px-lg-10">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="fs-6 fw-semibold mb-2">Celular</label>
                            <div class="form-control form-control-solid">{{ $modeloPersona->celular_persona ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="fs-6 fw-semibold mb-2">Correo</label>
                            <div class="form-control form-control-solid">{{ $modeloPersona->correo_persona ?? '-' }}</div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn d-flex align-items-center btn-light-secondary me-4" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>
