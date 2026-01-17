<div wire:ignore.self class="modal fade" id="modal-detalle-area" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">

            <div class="modal-header placeholder-glow">
                <h3 class="fw-bold my-0">
                    Detalles del área
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

                    <!-- INFORMACIÓN DEL ÁREA -->
                    <div class="fw-bold text-dark mb-3 mt-3">
                        <i class="ki-outline ki-abstract-47 me-2"></i> Información del área
                    </div>

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label class="form-label">Nombre del área</label>
                        <div class="form-control form-control-plaintext fw-bold text-dark">
                            {{ $areaDetalle?->nombre_area ?? 'N/A' }}
                        </div>
                    </div>

                    <!-- Siglas -->
                    <div class="mb-3">
                        <label class="form-label">Siglas</label>
                        <div class="form-control form-control-plaintext fw-bold text-dark">
                            {{ $areaDetalle?->siglas_area ?? 'N/A' }}
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <div class="form-control form-control-plaintext">
                            @if($areaDetalle && $areaDetalle->estado_area === \App\Enums\EstadoEnum::HABILITADO)
                                <span class="badge badge-success">Habilitado</span>
                            @else
                                <span class="badge badge-danger">Deshabilitado</span>
                            @endif
                        </div>
                    </div>

                    <!-- INFORMACIÓN DE AUDITORÍA -->
                    <div class="fw-bold text-dark mb-3 mt-5">
                        <i class="ki-outline ki-document me-2"></i> Información de auditoría
                    </div>

                    <!-- Creado -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Creado por</label>
                            <div class="form-control form-control-plaintext text-gray-700 fs-7">
                                {{ $areaDetalle?->au_usuariocr ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de creación</label>
                            <div class="form-control form-control-plaintext text-gray-700 fs-7">
                                @if($areaDetalle?->au_fechacr)
                                    {{ \Carbon\Carbon::parse($areaDetalle->au_fechacr)->format('d/m/Y H:i') }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Modificado -->
                    @if($areaDetalle?->au_fechamd)
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Modificado por</label>
                                <div class="form-control form-control-plaintext text-gray-700 fs-7">
                                    {{ $areaDetalle?->au_usuariomd ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de modificación</label>
                                <div class="form-control form-control-plaintext text-gray-700 fs-7">
                                    {{ \Carbon\Carbon::parse($areaDetalle->au_fechamd)->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Eliminado -->
                    @if($areaDetalle?->au_fechael)
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Eliminado por</label>
                                <div class="form-control form-control-plaintext text-gray-700 fs-7">
                                    {{ $areaDetalle?->au_usuarioel ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de eliminación</label>
                                <div class="form-control form-control-plaintext text-gray-700 fs-7">
                                    {{ \Carbon\Carbon::parse($areaDetalle->au_fechael)->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <div class="modal-footer d-flex justify-content-center">
                <button
                    type="reset"
                    class="btn d-flex align-items-center btn-primary"
                    data-bs-dismiss="modal"
                    aria-label="close"
                >
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
