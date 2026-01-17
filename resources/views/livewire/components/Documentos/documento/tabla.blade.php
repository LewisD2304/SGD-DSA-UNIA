<div>

    <div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="d-flex flex-wrap flex-stack my-5 mx-8 gap-3">
                    <div class="d-flex align-items-center position-relative my-1 fs-7">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" data-kt-user-table-filter="buscar" class="form-control form-control-solid ps-13 w-xl-300px w-250" placeholder="Buscar documento" wire:model.live.debounce.500ms="buscar" />
                    </div>

                    <div class="d-flex gap-2 my-1">
                        <input type="date" class="form-control form-control-solid" placeholder="Fecha inicio" wire:model.live="fechaInicio" max="{{ $fechaFin }}" title="Fecha de inicio" style="max-width: 150px;" />
                        <input type="date" class="form-control form-control-solid" placeholder="Fecha fin" wire:model.live="fechaFin" min="{{ $fechaInicio }}" title="Fecha fin" style="max-width: 150px;" />
                    </div>

                    <select class="form-select form-select-solid my-1" wire:model.live="idEstadoFiltro" style="max-width: 200px;">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $idEstado => $nombreEstado)
                        <option value="{{ $idEstado }}">{{ $nombreEstado }}</option>
                        @endforeach
                    </select>

                    <select class="form-select form-select-solid my-1" wire:model.live="idOficinaCatalogo" style="max-width: 250px;">
                        <option value="">Todas las oficinas</option>
                        @foreach($oficinas as $oficina)
                        <option value="{{ $oficina->id_catalogo }}">{{ ($oficina->abreviatura_catalogo ? $oficina->abreviatura_catalogo . ' - ' : '') . $oficina->descripcion_catalogo }}</option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-light-secondary fw-bold my-1" wire:click="limpiarFiltros">
                        <i class="ki-outline ki-trash fs-2"></i>
                        Limpiar
                    </button>

                    @can('autorizacion',['REGISTRAR','DOCUMENTOS'])
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary fw-bold" x-data @click="$dispatch('abrirModalDocumento')">
                            <i class="ki-outline ki-plus-circle fs-2"></i>
                            Nuevo
                        </button>
                    </div>
                    @endcan
                </div>

                <div class="card-body py-4">
                    <div class="dataTables_wrapper dt-bootstrap4 no-footer">

                        <div class="table-responsive" style="overflow: visible;">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                                <thead>
                                    <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                        <th class="w-10px pe-2">N°</th>
                                        <th class="min-w-125px">EXPEDIENTE</th>
                                        <th class="min-w-250px">ASUNTO</th>
                                        <th class="min-w-150px">REMITENTE</th>
                                        <th class="min-w-150px">DESTINO</th>
                                        <th class="min-w-150px">OFICINA</th>
                                        <th class="min-w-125px">FECHA DE CREACION</th>
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

                                        @php
                                        // Obtenemos el último movimiento registrado para ver quién lo envió por última vez
                                        $ultimoMovimiento = $documento->movimientos->sortByDesc('id_movimiento')->first();

                                        $nombreRemitenteActual = $ultimoMovimiento
                                        ? ($ultimoMovimiento->areaOrigen->nombre_area ?? 'Sin Área')
                                        : ($documento->areaRemitente->nombre_area ?? 'N/A');

                                        $nombreDestinoActual = $documento->areaDestino->nombre_area ?? 'N/A';
                                        @endphp

                                        <td>
                                            <div class="text-gray-800">{{ $nombreRemitenteActual }}</div>
                                            @if($documento->id_area_remitente != ($ultimoMovimiento->id_area_origen ?? 0) && $ultimoMovimiento)
                                            <span class="text-muted fs-9 d-block">Inicial: {{ $documento->areaRemitente->nombre_area ?? '' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-gray-800">{{ $nombreDestinoActual }}</div>
                                        </td>
                                        <td>
                                            @php
                                            $oficina = $documento->oficina;
                                            @endphp
                                            @if($oficina)
                                            <div class="text-gray-800">{{ $oficina->descripcion_catalogo }}</div>
                                            @if(!empty($oficina->abreviatura_catalogo))
                                            <span class="text-muted fs-9 d-block">{{ $oficina->abreviatura_catalogo }}</span>
                                            @endif
                                            @else
                                            <span class="text-muted">Sin oficina</span>
                                            @endif
                                        </td>
                                        <td>{{ formatoFechaText($documento->au_fechacr)}}</td>
                                        <td>
                                            @php
                                            // CALCULO DEL ESTADO VISUAL (LO QUE VE EL USUARIO)
                                            $areaUsuario = Auth::user()->persona->id_area ?? null;
                                            $estadoVisual = $documento->getEstadoVisual($areaUsuario);

                                            if ($estadoVisual) {
                                                // Usamos mb_strtoupper para asegurar que las tildes se conviertan bien (á -> Á)
                                                $nombreEstado = mb_strtoupper($estadoVisual->nombre_estado, 'UTF-8');

                                                // Mapeo de colores según catálogo entregado
                                                $colorEstado = match ($nombreEstado) {
                                                    'DERIVADO' => 'warning', // Amarillo
                                                    'RECEPCIONADO', 'OBSERVACION RECEPCIONADO' => 'warning', // Ámbar / Gold
                                                    'EN TRÁMITE', 'EN TRAMITE' => 'primary', // Azul
                                                    'OBSERVADO' => 'danger', // Rojo
                                                    'SUBSANADO' => 'success', // Verde claro
                                                    'ARCHIVADO' => 'primary', // Verde oscuro
                                                    'ANULADO' => 'danger', // Gris oscuro
                                                    'POR RECTIFICAR' => 'warning', // Naranja
                                                    default => 'secondary'
                                                };
                                            }
                                            @endphp

                                            @if(isset($estadoVisual))
                                            <span class="badge badge-light-{{ $colorEstado }} py-2 px-3">
                                                {{ $estadoVisual->nombre_estado }}
                                            </span>
                                            @else
                                            <span class="badge badge-light-secondary py-2 px-3">Sin estado</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button type="button" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm fs-6" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                    Acciones
                                                    <i class="ki-outline ki-down fs-5 ms-1"></i>
                                                </button>

                                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-6 py-4 w-175px" data-kt-menu="true">

                                                    @php
                                                    $estaArchivado = $documento->id_estado == 6;

                                                    // Usamos el estadoVisual calculado en la celda anterior para ser consistentes con lo que ve el usuario.
                                                    // Si no hay estado visual, usamos string vacío.
                                                    $nombreEstadoParaLogica = isset($estadoVisual) ? mb_strtoupper($estadoVisual->nombre_estado, 'UTF-8') : '';
                                                    $nombreEstadoParaLogica = trim($nombreEstadoParaLogica); // Quitamos espacios extra por si acaso

                                                    // Verificamos ambas formas (con y sin tilde) para seguridad
                                                    $estaEnTramite = ($nombreEstadoParaLogica === 'EN TRÁMITE' || $nombreEstadoParaLogica === 'EN TRAMITE');
                                                    // Solo permitir Ver y Anular cuando está POR RECTIFICAR
                                                    $estaPorRectificar = ($nombreEstadoParaLogica === 'POR RECTIFICAR');
                                                    // Ocultar acción Modificar cuando el documento está recepcionado en Mesa de Partes
                                                    $estaRecepcionado = ($nombreEstadoParaLogica === 'RECEPCIONADO');
                                                    $nombreAreaDestino = $documento->areaDestino->nombre_area ?? '';
                                                    $esDestinoMesaDePartes = (mb_strtoupper($nombreAreaDestino, 'UTF-8') === 'MESA DE PARTES');
                                                    $ocultarModificar = $esDestinoMesaDePartes && $estaRecepcionado;
                                                    @endphp

                                                    @can('autorizacion', ['VER', 'DOCUMENTOS'])
                                                    <div class="menu-item px-3">
                                                        <a href="#" class="menu-link px-3" wire:click="$dispatch('abrirModalDetalleDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                            <span class="menu-icon"><i class="ki-outline ki-eye fs-3"></i></span> Ver
                                                        </a>
                                                    </div>
                                                    @endcan

                                                    @if(!$estaArchivado && !$estaEnTramite && !$estaPorRectificar)
                                                        @can('autorizacion', ['RESPONDER', 'DOCUMENTOS'])
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3" wire:click="$dispatch('abrirModalResponderDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                                <span class="menu-icon">
                                                                    <i class="ki-outline ki-send fs-3"></i>
                                                                </span>
                                                                Responder
                                                            </a>
                                                        </div>
                                                        @endcan
                                                    @endif

                                                    @if(!$estaArchivado && !$estaEnTramite && !$ocultarModificar && !$estaPorRectificar)
                                                        @can('autorizacion', ['MODIFICAR', 'DOCUMENTOS'])
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3" wire:click="$dispatch('abrirModalDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                                <span class="menu-icon"><i class="ki-outline ki-pencil fs-3"></i></span> Modificar
                                                            </a>
                                                        </div>
                                                        @endcan
                                                    @endif

                                                    @if(!$estaArchivado)
                                                        @can('autorizacion', ['ANULAR', 'DOCUMENTOS'])
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3 text-danger" wire:click="$dispatch('abrirModalAnularDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                                <span class="menu-icon"><i class="ki-outline ki-cross-circle fs-3 text-danger"></i></span> Anular
                                                            </a>
                                                        </div>
                                                        @endcan
                                                    @endif

                                                    @php
                                                    // Ocultar Observar si el documento ya está en OBSERVACION RECEPCIONADO
                                                    $esObservacionRecepcionado = str_contains($nombreEstadoParaLogica, 'OBSERVACION') && str_contains($nombreEstadoParaLogica, 'RECEPCIONADO');
                                                    @endphp

                                                    @if(!$estaArchivado && !$estaEnTramite && !$estaPorRectificar && !$esObservacionRecepcionado)
                                                        @can('autorizacion', ['OBSERVAR', 'DOCUMENTOS'])
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3 text-warning" wire:click="$dispatch('abrirModalObservarDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                                <span class="menu-icon"><i class="ki-outline ki-eye-slash fs-3 text-warning"></i></span> Observar
                                                            </a>
                                                        </div>
                                                        @endcan
                                                    @endif

                                                    @if(Auth::user()->can('autorizacion', ['DERIVAR', 'DOCUMENTOS']) || Auth::user()->can('autorizacion', ['ARCHIVAR', 'DOCUMENTOS']))
                                                    <div class="separator my-2"></div>
                                                    @endif

                                                    @php
                                                    $areaUsuario = Auth::user()->persona->id_area ?? null;

                                                    $puedeDerivar = $areaUsuario && $documento->id_area_destino == $areaUsuario;

                                                    // Solo se puede archivar si está recepcionado
                                                    $nombreEstadoParaArchivar = isset($estadoVisual) ? mb_strtoupper($estadoVisual->nombre_estado, 'UTF-8') : '';
                                                    $puedeArchivar = $areaUsuario &&
                                                                     $documento->id_area_destino == $areaUsuario &&
                                                                     $nombreEstadoParaArchivar === 'RECEPCIONADO';
                                                    @endphp



                                                    @can('autorizacion', ['ARCHIVAR', 'DOCUMENTOS'])
                                                        @if($puedeArchivar && !$estaPorRectificar)
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3 text-warning" wire:click="abrirModalArchivar({{ $documento->id_documento }})">
                                                                <span class="menu-icon"><i class="ki-outline ki-archive fs-3 text-warning"></i></span> Archivar
                                                            </a>
                                                        </div>
                                                        @endif
                                                    @endcan

                                                    @if($estaArchivado)
                                                    <div class="separator my-2"></div>
                                                        @can('autorizacion', ['RECTIFICAR', 'DOCUMENTOS'])
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3 text-warning" wire:click="$dispatch('abrirModalRectificarDocumento', { id_documento: {{ $documento->id_documento }} })">
                                                                <span class="menu-icon">
                                                                    <i class="ki-outline ki-pencil fs-4 text-warning"></i>
                                                                </span>
                                                                Rectificar
                                                            </a>
                                                        </div>
                                                        @endcan
                                                    @endif

                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-8 text-muted">
                                            <div x-data="{ cargado: false }" x-init="cargado = true">
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

                            <div class="mt-4">
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

    <div wire:ignore.self class="modal fade" id="modal-archivar-documento" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="fw-bold my-0">Confirmar archivado</h3>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="confirmarArchivar">
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
                                    Esta acción <strong>archivará</strong> el documento. El documento quedará finalizado y solo podrá ser visualizado.
                                </p>
                                <div class="d-flex justify-content-center mt-7">
                                    <div class="fw-bold">Documento:</div>
                                    <div class="px-2 text-gray-700 text-start">{{ $documentoArchivarTitulo }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn d-flex align-items-center btn-light-secondary me-4" data-bs-dismiss="modal" aria-label="cancel">
                            Cancelar
                        </button>
                        <button type="submit" class="btn d-flex align-items-center btn-warning" wire:loading.attr="disabled" wire:target="confirmarArchivar">
                            <span class="indicator-label" wire:loading.remove wire:target="confirmarArchivar">
                                Archivar
                            </span>
                            <span class="indicator-progress" wire:loading wire:target="confirmarArchivar">
                                Cargando...
                                <span><x-spinner style="width: 20px; height: 20px;" /></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('livewire.components.Documentos.documento.modal-observacion-documento')

</div>
