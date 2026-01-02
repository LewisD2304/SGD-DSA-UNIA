@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
@endphp

{{-- Usamos mw-1000px para que el diseño a 2 columnas tenga espacio suficiente --}}
<div wire:ignore.self class="modal fade" id="modal-detalle-documento" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-1000px">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold text-gray-900 m-0">
                    Vista Previa del Documento
                </h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            {{-- BODY --}}
            <div class="modal-body pt-3 pb-5 px-5 px-lg-10">
                @if ($modeloDocumento)

                {{-- ======================================================= --}}
                {{-- LÓGICA PHP (Mantenemos tu lógica original)              --}}
                {{-- ======================================================= --}}
                @php
                    // 1. Contexto
                    $idAreaActualUsuario = Auth::user()->persona->id_area ?? null;
                    $nombreEstadoActual = strtoupper($modeloDocumento->estado->nombre_estado ?? '');

                    // 2. Rectificación
                    $esSolicitudRectificacion = ($modeloDocumento->id_estado == 10) || str_contains($nombreEstadoActual, 'RECTIFIC');
                    $ultimoMovimiento = $modeloDocumento->movimientos()->latest('au_fechacr')->first();
                    $motivoRectificacion = $esSolicitudRectificacion && $ultimoMovimiento ? $ultimoMovimiento->observacion_doc_movimiento : null;

                    // 3. Observación
                    $movObservado = $modeloDocumento->movimientos()
                        ->whereHas('estado', function($q){ $q->where('nombre_estado','OBSERVADO'); })
                        ->latest('au_fechacr')->first();
                    $motivoObservacion = $movObservado?->observacion_doc_movimiento;
                    $idAreaQueObservo = $movObservado?->id_area_origen;

                    // 4. Visibilidad Alertas
                    $mostrarAlertaRoja = ($nombreEstadoActual === 'OBSERVADO');
                    $estaSubsanado = ($nombreEstadoActual === 'SUBSANADO' || $nombreEstadoActual === 'RECEPCION SUBSANADA');
                    $soyElObservador = ($idAreaActualUsuario == $idAreaQueObservo);
                    $mostrarSubsanacion = $estaSubsanado && $soyElObservador;

                    // 5. Archivos (Separamos evidencias de archivos normales)
                    $archivos = $modeloDocumento->archivos ?? collect();
                    $archivosEvidencia = $archivos->where('tipo_archivo', 'evidencia_rectificacion');
                    $archivosEvidenciaObs = $archivos->where('tipo_archivo', 'evidencia_observacion');

                    // Archivos Documentales (Originales + Anexos normales)
                    $idsEvidencia = $archivosEvidencia->pluck('id_archivo_documento')
                        ->merge($archivosEvidenciaObs->pluck('id_archivo_documento'))->toArray();

                    $archivosDelDocumento = $archivos->whereNotIn('id_archivo_documento', $idsEvidencia);
                @endphp

                <div class="row g-0">

                    {{-- ====================================================== --}}
                    {{-- COLUMNA IZQUIERDA: INFORMACIÓN DEL DOCUMENTO           --}}
                    {{-- ====================================================== --}}
                    <div class="col-lg-7 pe-lg-5 mb-5 mb-lg-0 border-end border-gray-200">

                        {{-- Encabezado Documento --}}
                        <div class="d-flex align-items-center mb-5">
                            <div class="symbol symbol-50px me-3">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-outline ki-document fs-1 text-primary"></i>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-500 fs-7 fw-bold text-uppercase">Documento N°</span>
                                <h5 class="fw-bold text-dark mb-0">{{ $modeloDocumento->numero_documento }}</h5>
                                <span class="badge badge-light-secondary fs-8 mt-1 w-auto">Exp: {{ $modeloDocumento->expediente_documento ?? 'S/N' }}</span>
                            </div>
                        </div>

                        {{-- Grid de Datos Pequeños --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="bg-light rounded p-2 border border-dashed border-gray-300">
                                    <div class="text-gray-500 fw-bold fs-9 text-uppercase">Tipo</div>
                                    <div class="text-gray-800 fw-bold fs-7 text-truncate" title="{{ $modeloDocumento->tipoDocumento->descripcion_catalogo ?? '' }}">
                                        {{ $modeloDocumento->tipoDocumento->descripcion_catalogo ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded p-2 border border-dashed border-gray-300">
                                    <div class="text-gray-500 fw-bold fs-9 text-uppercase">Folios</div>
                                    <div class="text-gray-800 fw-bold fs-7">{{ $modeloDocumento->folio_documento }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded p-2 border border-dashed border-gray-300">
                                    <div class="text-gray-500 fw-bold fs-9 text-uppercase">Fecha</div>
                                    <div class="text-gray-800 fw-bold fs-7">{{ Carbon::parse($modeloDocumento->au_fechacr)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Remitente y Recepción --}}
                        <div class="border border-gray-200 rounded p-3 mb-4">
                            <div class="row">
                                <div class="col-6 border-end">
                                    <span class="text-gray-500 fw-bold fs-9 text-uppercase d-block mb-1">De: Remitente Inicial</span>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-25px me-2">
                                            <span class="symbol-label bg-dark text-white fs-8 fw-bold">
                                                {{ substr($modeloDocumento->areaRemitente->nombre_area ?? 'M', 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="text-gray-800 fw-semibold fs-7 lh-1">{{ $modeloDocumento->areaRemitente->nombre_area ?? 'Mesa de Partes' }}</span>
                                    </div>
                                </div>
                                <div class="col-6 ps-4">
                                    <span class="text-gray-500 fw-bold fs-9 text-uppercase d-block mb-1">Recepción</span>
                                    @if($modeloDocumento->fecha_recepcion_documento)
                                        <span class="text-success fw-bold fs-7">
                                            <i class="ki-outline ki-check-circle fs-6 me-1"></i>
                                            {{ Carbon::parse($modeloDocumento->fecha_recepcion_documento)->format('d/m/Y H:i') }}
                                        </span>
                                    @else
                                        <span class="badge badge-light-warning">Pendiente</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Asunto --}}
                        <div class="mb-4">
                            <label class="fw-bold text-gray-600 fs-9 mb-1 text-uppercase">Asunto:</label>
                            <div class="form-control form-control-solid bg-white border border-gray-300 text-gray-800 fs-6">
                                {{ $modeloDocumento->asunto_documento }}
                            </div>
                        </div>

                        {{-- Nota Inicial / Descripción (AZUL) --}}
                        @if($modeloDocumento->observacion_documento)
                        <div class="mb-4">
                            <label class="fw-bold text-primary fs-9 mb-1 text-uppercase">
                                <i class="ki-outline ki-note-2 fs-8 text-primary me-1"></i>
                                Nota Inicial / Descripción:
                            </label>
                            <div class="alert alert-dismissible bg-light-primary border border-primary border-dashed p-3 m-0">
                                <div class="fs-7 text-gray-700">{{ $modeloDocumento->observacion_documento }}</div>
                            </div>
                        </div>
                        @endif

                        {{-- Archivos del Documento --}}
                        <div>
                            <label class="fw-bold text-gray-600 fs-9 mb-2 text-uppercase">Archivos del Documento</label>
                            @if($archivosDelDocumento->count() > 0)
                                <div class="d-flex flex-column gap-2">
                                    @foreach($archivosDelDocumento as $archivo)
                                    <div class="d-flex align-items-center bg-white border border-gray-300 rounded p-2 px-3 shadow-sm">
                                        <i class="ki-outline {{ $archivo->icono ?? 'ki-file' }} fs-2 text-primary me-3"></i>
                                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                                            <span class="text-gray-800 fw-bold fs-7 text-truncate">{{ $archivo->nombre_original }}</span>
                                            <span class="text-muted fs-9">{{ $archivo->tamanio_formateado }}</span>
                                        </div>
                                        <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-icon btn-sm btn-light-primary">
                                            <i class="ki-outline ki-eye fs-4"></i>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted fs-7 fst-italic">Sin archivos adjuntos.</div>
                            @endif
                        </div>

                    </div>

                    {{-- ====================================================== --}}
                    {{-- COLUMNA DERECHA: ESTADO E INCIDENCIAS                  --}}
                    {{-- ====================================================== --}}
                    <div class="col-lg-5 ps-lg-5">

                        {{-- Bloque de Estado --}}
                        <div class="text-center mb-5 mt-4">
                            <small class="text-gray-500 fw-bold text-uppercase letter-spacing-1">Estado Actual</small>
                            <div class="mt-2">
                                @php
                                    $colorEstado = match($nombreEstadoActual) {
                                        'RECEPCIONADO' => 'success',
                                        'OBSERVADO' => 'danger',
                                        'DERIVADO' => 'secondary',
                                        'ARCHIVADO' => 'primary',
                                        'SUBSANADO', 'RECEPCION SUBSANADA' => 'success',
                                        default => 'info'
                                    };
                                @endphp
                                <span class="badge badge-light-{{ $colorEstado }} fs-4 fw-bolder py-3 px-4 shadow-sm">
                                    {{ $modeloDocumento->estado->nombre_estado }}
                                </span>
                            </div>
                        </div>

                        {{-- Contenedor de Alertas / Incidencias --}}
                        <div class="d-flex flex-column gap-4">

                            {{-- 1. ALERTA ROJA (OBSERVACIÓN) --}}
                            @if($mostrarAlertaRoja && $motivoObservacion)
                                <div class="notice d-flex bg-light-danger rounded border-start border-4 border-danger p-4 shadow-sm">
                                    <div class="d-flex flex-column w-100">
                                        <h5 class="text-danger fw-bold mb-2">
                                            <i class="ki-outline ki-eye-slash fs-2 text-danger me-2"></i>
                                            Observación
                                        </h5>
                                        <div class="fs-7 text-gray-800 bg-white rounded p-3 border border-danger border-dashed">
                                            {{ $motivoObservacion }}
                                        </div>

                                        {{-- Evidencia de Observación --}}
                                        @if($archivosEvidenciaObs->count() > 0)
                                            <div class="mt-3">
                                                <span class="text-danger fw-bold fs-9 text-uppercase mb-2 d-block">Evidencia del error:</span>
                                                @foreach($archivosEvidenciaObs as $archivo)
                                                    <div class="d-flex align-items-center bg-white border border-danger rounded p-2 mb-2">
                                                        <i class="ki-outline ki-file fs-4 text-danger me-2"></i>
                                                        <span class="fs-8 text-truncate flex-grow-1">{{ $archivo->nombre_original }}</span>
                                                        <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="text-danger hover-underline fs-8 fw-bold">Ver</a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- 2. ALERTA AMARILLA (RECTIFICACIÓN) --}}
                            @if($motivoRectificacion)
                                <div class="notice d-flex bg-light-warning rounded border-start border-4 border-warning p-4 shadow-sm">
                                    <div class="d-flex flex-column w-100">
                                        <h5 class="text-gray-900 fw-bold mb-2">
                                            <i class="ki-outline ki-information fs-2 text-warning me-2"></i>
                                            Motivo Rectificación
                                        </h5>
                                        <div class="fs-7 text-gray-700 bg-white rounded p-3 border border-warning border-dashed">
                                            {{ $motivoRectificacion }}
                                        </div>

                                        @if($archivosEvidencia->count() > 0)
                                            <div class="mt-3">
                                                <span class="text-warning fw-bold fs-9 text-uppercase mb-2 d-block">Evidencia:</span>
                                                @foreach($archivosEvidencia as $archivo)
                                                    <div class="d-flex align-items-center bg-white border border-warning rounded p-2 mb-2">
                                                        <i class="ki-outline ki-file fs-4 text-warning me-2"></i>
                                                        <span class="fs-8 text-truncate flex-grow-1">{{ $archivo->nombre_original }}</span>
                                                        <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="text-warning hover-underline fs-8 fw-bold">Ver</a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- 3. ALERTA VERDE (SUBSANADO) --}}
                            @if($mostrarSubsanacion)
                                <div class="notice d-flex bg-light-success rounded border-start border-4 border-success p-4 shadow-sm">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-success fw-bold mb-1">
                                            <i class="ki-outline ki-check-circle fs-2 text-success me-2"></i>
                                            Subsanado
                                        </h5>
                                        <div class="fs-7 text-gray-600">
                                            La observación ha sido corregida.
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- ESTADO LIMPIO (SIN INCIDENCIAS) --}}
                            @if(!$motivoRectificacion && !($mostrarAlertaRoja && $motivoObservacion) && !$mostrarSubsanacion)
                                <div class="text-center py-5 mt-5 opacity-75">
                                    <div class="mb-3">
                                        <i class="ki-outline ki-shield-tick fs-4x text-gray-300"></i>
                                    </div>
                                    <div class="text-gray-400 fs-7 fw-bold">Sin incidencias ni observaciones</div>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>
                @endif
            </div>

            <div class="modal-footer border-0 pt-0 justify-content-end">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
