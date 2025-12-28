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

