<div>
    <div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="d-flex flex-wrap flex-stack my-5 mx-8">
                    <div class="d-flex align-items-center position-relative my-1 me-4 fs-7">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" data-kt-user-table-filter="buscar" class="form-control form-control-solid ps-13 w-xl-350px w-300" placeholder="Buscar documento" wire:model.live.debounce.500ms="buscar" />
                    </div>
                </div>

                <div class="card-body py-4">
                    <div lass="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                                <thead>
                                    <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                        <th class="w-10px pe-2">N°</th>
                                        <th class="min-w-125px">EXPEDIENTE</th>
                                        <th class="min-w-250px">ASUNTO</th>
                                        <th class="min-w-150px">REMITENTE</th>
                                        <th class="min-w-150px">DESTINO</th>
                                        <th class="min-w-125px">FECHA CREACIÓN</th>
                                        <th class="min-w-100px">ESTADO</th>
                                        <th class="text-center min-w-100px">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600" wire:loading.class="opacity-25" wire:target="buscar, gotoPage, previousPage, nextPage">
                                    @php
                                    $contador = $this->documentos->firstItem();
                                    @endphp
                                    @forelse ($this->documentos as $documento)
                                    <tr wire:key="documento-{{ $documento->id_documento }}">
                                        <td>{{ $contador++ }}</td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $documento->expediente_documento }}</div>
                                            @if($documento->numero_documento)
                                            <div class="text-muted fs-7">N°: {{ $documento->numero_documento }}</div>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="text-gray-800">{{ Str::limit($documento->asunto_documento, 60) }}</div>
                                        </td>

                                        <td>
                                            <div class="text-gray-800">{{ $documento->areaRemitente->nombre_area ?? 'N/A' }}</div>
                                        </td>

                                        <td>
                                            <div class="text-gray-800">{{ $documento->areaDestino->nombre_area ?? 'N/A' }}</div>
                                        </td>
                                        <td>{{ formatoFechaText($documento->au_fechacr)}}</td>

                                        <td>
                                            @if($documento->estado)
                                            @php
                                            $nombreEstado = strtoupper($documento->estado->nombre_estado);
                                            $colorEstado = match($nombreEstado) {
                                            'RECEPCIONADO' => 'success',
                                            'OBSERVADO' => 'danger',
                                            'DERIVADO' => 'secondary',
                                            'ARCHIVADO' => 'primary',
                                            default => 'info'
                                            };
                                            @endphp
                                            <span class="badge badge-light-{{ $colorEstado }} py-2 px-3">
                                                {{ $documento->estado->nombre_estado }}
                                            </span>
                                            @else
                                            <span class="badge badge-light-secondary py-2 px-3">Sin estado</span>
                                            @endif </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                @can('autorizacion',['VER','DOCUMENTOS'])
                                                <button type="button" class="btn btn-light btn-sm" wire:click="$dispatch('abrirModalDetalleDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                    <i class="ki-outline ki-eye fs-4 me-1"></i> Ver
                                                </button>
                                                @endcan

                                                @php
                                                $nombreEstado = strtoupper($documento->estado->nombre_estado ?? '');
                                                $esSolicitudRectificacion = ($documento->id_estado == 10) || str_contains($nombreEstado, 'RECTIFIC');
                                                @endphp

                                                @if($esSolicitudRectificacion)
                                                @can('autorizacion',['RECTIFICAR','DOCUMENTOS'])
                                                <button type="button" class="btn btn-success btn-sm" wire:click="abrirRectificacion({{ $documento->id_documento }}, 'aceptar')">
                                                    <i class="ki-outline ki-check fs-4 me-1"></i> Aceptar
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" wire:click="abrirRectificacion({{ $documento->id_documento }}, 'rechazar')">
                                                    <i class="ki-outline ki-cross fs-4 me-1"></i> Rechazar
                                                </button>
                                                @endcan
                                                @else
                                                @php
                                                // Verificar si el documento viene de vuelta a Mesa de Partes (área creadora original)
                                                $areaUsuario = Auth::user()->persona->id_area ?? null;
                                                $esCreadorOriginal = ($documento->id_area_remitente == $areaUsuario &&
                                                $documento->id_area_destino == $areaUsuario);

                                                $transiciones = $this->obtenerTransicionesDisponibles($documento->id_estado);

                                                // Mesa de Partes: primero recepciona, luego archiva cuando el estado ya es RECEPCIONADO
                                                if ($esCreadorOriginal) {
                                                $transiciones = $transiciones->filter(function($t) use ($nombreEstado) {
                                                $evento = strtoupper($t->evento_transicion);
                                                if ($nombreEstado === 'RECEPCIONADO') {
                                                    return in_array($evento, ['ARCHIVADO', 'ARCHIVAR']);
                                                }
                                                if ($nombreEstado === 'OBSERVADO') {
                                                    return $evento === 'OBSERVACION RECEPCIONADO';
                                                }
                                                if ($nombreEstado === 'RECEPCION SUBSANADA' || $nombreEstado === 'SUBSANADO') {
                                                    return $evento === 'RECEPCIONAR SUBSANACION';
                                                }
                                                return $evento === 'RECEPCIONAR';
                                                });
                                                } else {
                                                // Para otras áreas, EXCLUIR las transiciones EN TRAMITE y ARCHIVADO
                                                $transiciones = $transiciones->filter(function($t) {
                                                $evento = strtoupper($t->evento_transicion);
                                                return !in_array($evento, ['EN TRAMITE', 'EN TRÁMITE', 'ARCHIVADO']);
                                                });
                                                }
                                                @endphp

                                                @foreach($transiciones as $transicion)
                                                @php
                                                $evento = strtoupper($transicion->evento_transicion);
                                                $accion = strtolower($evento);

                                                $config = [
                                                'RECEPCIONAR' => ['color' => 'success', 'icono' => 'folder-check', 'texto' => 'Recepcionar'],
                                                    'OBSERVACION RECEPCIONADO' => ['color' => 'success', 'icono' => 'folder-check', 'texto' => 'Recepcionar observado'],
                                                'RECEPCIONAR SUBSANACION' => ['color' => 'success', 'icono' => 'shield-tick', 'texto' => 'Recepcionar subsanación'],
                                                'ARCHIVADO' => ['color' => 'warning', 'icono' => 'archive', 'texto' => 'Archivar'],
                                                'ARCHIVAR' => ['color' => 'warning', 'icono' => 'archive', 'texto' => 'Archivar'],
                                                'DEVOLVER' => ['color' => 'danger', 'icono' => 'arrow-left', 'texto' => 'Devolver'],
                                                'DERIVAR' => ['color' => 'primary', 'icono' => 'arrow-right', 'texto' => 'Derivar'],
                                                'SUBSANAR' => ['color' => 'warning', 'icono' => 'document-check', 'texto' => 'Subsanar']
                                                ];

                                                $btnConfig = $config[$evento] ?? ['color' => 'secondary', 'icono' => 'abstract-26', 'texto' => $evento];
                                                @endphp

                                                @if($evento === 'RECEPCIONAR' || $evento === 'OBSERVACION RECEPCIONADO' || $evento === 'RECEPCIONAR SUBSANACION')
                                                <button type="button" class="btn btn-{{ $btnConfig['color'] }} btn-sm" wire:click="solicitarRecepcion({{ $documento->id_documento }}, false)">
                                                    <i class="ki-outline ki-{{ $btnConfig['icono'] }} fs-4 me-1"></i>
                                                    {{ $btnConfig['texto'] }}
                                                </button>
                                                @elseif($evento === 'ARCHIVADO' || $evento === 'ARCHIVAR')
                                                <button type="button" class="btn btn-{{ $btnConfig['color'] }} btn-sm" wire:click="solicitarRecepcion({{ $documento->id_documento }}, true)">
                                                    <i class="ki-outline ki-{{ $btnConfig['icono'] }} fs-4 me-1"></i>
                                                    {{ $btnConfig['texto'] }}
                                                </button>
                                                @else
                                                <button type="button" class="btn btn-{{ $btnConfig['color'] }} btn-sm" wire:click="$dispatch('abrirModalAccion', { id_documento: {{ $documento->id_documento }}, accion: '{{ $accion }}' })">
                                                    <i class="ki-outline ki-{{ $btnConfig['icono'] }} fs-4 me-1"></i>
                                                    {{ $btnConfig['texto'] }}
                                                </button>
                                                @endif @endforeach @endif </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-8 text-muted">
                                            <div x-data="{ cargado: false, modo: localStorage.getItem('data-bs-theme-mode') || 'light' }" x-init="cargado = true">
                                                <template x-if="cargado">
                                                    <x-blank-state-table mensaje="No se encontraron registros" />
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="position-absolute top-50 start-50 translate-middle" style="margin-top: 1.06rem;" wire:loading wire:target="buscar, gotoPage, previousPage, nextPage">
                                <x-spinner class="text-primary" style="width: 35px; height: 35px;" />
                            </div>

                            <div>
                                @if ($this->documentos->hasPages())
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex align-items-center">Mostrando {{ $this->documentos->firstItem() }} - {{ $this->documentos->lastItem() }} de {{ $this->documentos->total() }} registros</div>
                                    <div class="pagination pagination-lg">{{ $this->documentos->links() }}</div>
                                </div>
                                @else
                                <div class="d-flex justify-content-between py-2">
                                    <div class="d-flex align-items-center text-muted">Mostrando {{ $this->documentos->firstItem() }} - {{ $this->documentos->lastItem() }} de {{ $this->documentos->total() }} registros</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modal-confirmar-recepcion" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header placeholder-glow">
                    <h3 class="fw-bold my-0">Confirmar recepción</h3>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="confirmarRecepcion">

                    <div class="modal-body px-5">
                        <div class="d-flex flex-column px-5 ">

                            <div class="modal-header text-center flex-column border-0">
                                <p>
                                    <i class="ki-duotone ki-information-5 text-warning" style="font-size: 7rem !important;">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </p>
                                <h4 class="modal-title w-100 mt-5">
                                    ¿Estás seguro de realizar esta acción?
                                </h4>
                            </div>

                            <div class="px-4 text-center fs-5">
                                <p class="text-gray-700">
                                    @if($esArchivar)
                                    Esta acción <strong>archivará</strong> el documento. El documento quedará finalizado y solo podrá ser visualizado.
                                    @else
                                    Esta acción recepcionará el documento y lo moverá a "Mis documentos".
                                    @endif
                                </p>

                                <div class="d-flex justify-content-center mt-7">
                                    <div class="fw-bold">Documento:</div>
                                    <div class="px-2 text-gray-700 text-start">{{ $documentoRecepcionTitulo }}</div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn d-flex align-items-center btn-light-secondary me-4" data-bs-dismiss="modal" aria-label="cancel">
                            Cancelar
                        </button>

                        <button type="submit" class="btn d-flex align-items-center btn-warning" wire:loading.attr="disabled" wire:target="confirmarRecepcion">
                            <span class="indicator-label" wire:loading.remove wire:target="confirmarRecepcion">
                                {{ $esArchivar ? 'Archivar' : 'Recepcionar' }}
                            </span>
                            <span class="indicator-progress" wire:loading wire:target="confirmarRecepcion">
                                Cargando...
                                <span>
                                    <x-spinner style="width: 20px; height: 20px;" />
                                </span>
                            </span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modal-rectificacion" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered mw-600px">
            <div class="modal-content">

                <div class="modal-header">
                    <h3 class="fw-bold my-0">
                        {{ $accionRectificacion === 'rechazar' ? 'Rechazar rectificación' : 'Aceptar rectificación' }}
                    </h3>

                    <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <form autocomplete="off" novalidate class="form" wire:submit.prevent="confirmarRectificacion">
                    <div class="modal-body px-5">
                        <div class="d-flex flex-column px-5 px-lg-10">

                            @if($accionRectificacion === 'aceptar')
                            <div class="alert alert-success d-flex align-items-center mb-4">
                                <i class="ki-outline ki-check-circle fs-2x text-success me-3"></i>
                                <div>
                                    <h5 class="mb-1">¿Confirmar aceptación?</h5>
                                    <p class="mb-0">El documento cambiará a estado "POR RECTIFICAR" y se notificará al área correspondiente.</p>
                                </div>
                            </div>
                            @else
                            <p class="text-gray-700 mb-4">
                                Esta acción rechazará la solicitud de rectificación y archivará el documento.
                            </p>

                            <div class="fv-row mb-5">
                                <label class="required fw-semibold fs-6 mb-2">Motivo del rechazo</label>
                                <textarea wire:model="motivoRectificacion" class="form-control form-control-solid" rows="4" placeholder="Explica por qué se rechaza la solicitud" maxlength="500"></textarea>
                                <div class="text-muted fs-8 mt-1">
                                    {{ strlen($motivoRectificacion) }}/500 caracteres
                                </div>
                                @error('motivoRectificacion')
                                <span class="text-danger fs-7">{{ $message }}</span>
                                @enderror
                            </div>
                            @endif

                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label" wire:loading.remove wire:target="confirmarRectificacion">
                                Confirmar
                            </span>
                            <span class="indicator-progress" wire:loading wire:target="confirmarRectificacion">
                                Procesando
                                <span>
                                    <x-spinner style="width: 20px; height: 20px;" /></span>
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
