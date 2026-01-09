@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;
    use Illuminate\Support\Str;
@endphp

{{-- Aumentamos el ancho a 1000px para acomodar las dos columnas --}}
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
                {{-- LÓGICA PHP: SEPARACIÓN DE DATOS (DOCUMENTO VS FLUJO)    --}}
                {{-- ======================================================= --}}
                @php
                    // 1. Contexto del Usuario
                    $idAreaActualUsuario = Auth::user()->persona->id_area ?? null;
                    $nombreEstadoActual = strtoupper($modeloDocumento->estado->nombre_estado ?? '');

                    // 2. OBTENER EL ÚLTIMO MOVIMIENTO (Para ver el flujo actual)
                    $ultimoMovimiento = $modeloDocumento->movimientos()->latest('au_fechacr')->first();

                    // 3. LOGICA DE OBSERVACIÓN (ALERTA ROJA - DERECHA)
                    // Buscamos específicamente el movimiento donde se generó el estado "OBSERVADO"
                    // para no confundirlo con la nota inicial del documento.
                    $movObservado = $modeloDocumento->movimientos()
                        ->whereHas('estado', function($q){ $q->where('nombre_estado','OBSERVADO'); })
                        ->latest('au_fechacr')->first();

                    $motivoObservacion = $movObservado ? $movObservado->observacion_doc_movimiento : null;
                    $fechaObservacion = $movObservado ? $movObservado->au_fechacr : null;
                    $idAreaQueObservo = $movObservado ? $movObservado->id_area_origen : null;

                    // ¿Debemos mostrar la alerta roja?
                    $esPendientes = request()->routeIs('documentos.pendientes.*');
                    $estaRecepcionado = $modeloDocumento->fecha_recepcion_documento != null;
                    $esEstadoObservado = ($nombreEstadoActual === 'OBSERVADO');
                    $esObservacionRecepcionado = str_contains($nombreEstadoActual, 'OBSERVACION') && str_contains($nombreEstadoActual, 'RECEPCIONADO');

                    // Lógica de visibilidad:
                    // - Si estado es "OBSERVACION RECEPCIONADO": SIEMPRE mostrar (ya fue recepcionado)
                    // - Si estado es "OBSERVADO" en Pendientes: SIEMPRE mostrar
                    // - Si estado es "OBSERVADO" en Mis Documentos: SOLO mostrar si ya recepcionó
                    $mostrarAlertaRoja = false;
                    if (!empty($motivoObservacion)) {
                        if ($esObservacionRecepcionado) {
                            $mostrarAlertaRoja = true; // OBSERVACION RECEPCIONADO siempre muestra
                        } elseif ($esEstadoObservado) {
                            if ($esPendientes) {
                                $mostrarAlertaRoja = true; // En pendientes siempre mostrar
                            } else {
                                $mostrarAlertaRoja = $estaRecepcionado; // En Mis Documentos solo si ya recepcionó
                            }
                        }
                    }

                    // 4. LÓGICA DE RECTIFICACIÓN (ALERTA AMARILLA - DERECHA)
                    // Si el estado actual sugiere rectificación, tomamos el comentario del último movimiento
                    $esSolicitudRectificacion = ($modeloDocumento->id_estado == 10) || str_contains($nombreEstadoActual, 'RECTIFIC');
                    $motivoRectificacion = ($esSolicitudRectificacion && $ultimoMovimiento) ? $ultimoMovimiento->observacion_doc_movimiento : null;

                    // 5. LÓGICA DE SUBSANACIÓN (ALERTA VERDE - DERECHA)
                    $estaSubsanado = ($nombreEstadoActual === 'SUBSANADO' || $nombreEstadoActual === 'RECEPCION SUBSANADA');
                    $soyElObservador = ($idAreaActualUsuario == $idAreaQueObservo);
                    $mostrarSubsanacion = $estaSubsanado && $soyElObservador;

                    // 6. ARCHIVOS
                    $archivos = $modeloDocumento->archivos ?? collect();
                    $archivosOriginales = $archivos->where('tipo_archivo', 'original');
                    $archivosEvidencia = $archivos->where('tipo_archivo', 'evidencia_rectificacion');
                    $archivosEvidenciaObservacion = $archivos->where('tipo_archivo', 'evidencia_observacion');

                    $idsClasificados = $archivosOriginales->pluck('id_archivo_documento')
                        ->merge($archivosEvidencia->pluck('id_archivo_documento'))
                        ->merge($archivosEvidenciaObservacion->pluck('id_archivo_documento'))
                        ->toArray();
                    $archivosOtros = $archivos->whereNotIn('id_archivo_documento', $idsClasificados);
                @endphp

                <div class="row g-0">

                    {{-- ========================================== --}}
                    {{-- COLUMNA IZQUIERDA: DATOS FIJOS DEL DOC     --}}
                    {{-- ========================================== --}}
                    <div class="col-lg-7 pe-lg-5 mb-5 mb-lg-0">

                        <div class="d-flex align-items-center mb-5">
                            <div class="symbol symbol-50px me-3">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-outline ki-document fs-1 text-primary"></i>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-500 fs-7 fw-bold text-uppercase">Documento N°</span>
                                <span class="text-gray-900 fs-3 fw-bolder">{{ $modeloDocumento->numero_documento ?? 'PENDIENTE' }}</span>
                                @if($modeloDocumento->expediente_documento)
                                    <span class="badge badge-light-secondary fs-8 mt-1">Exp: {{ $modeloDocumento->expediente_documento }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mb-5">
                            <div class="col-sm-4">
                                <div class="bg-light rounded p-3 h-100">
                                    <div class="text-gray-500 fw-bold fs-9 text-uppercase mb-1">Tipo</div>
                                    <div class="text-gray-800 fw-bold fs-7">{{ $modeloDocumento->tipoDocumento->descripcion_catalogo ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="bg-light rounded p-3 h-100">
                                    <div class="text-gray-500 fw-bold fs-9 text-uppercase mb-1">Folios</div>
                                    <div class="text-gray-800 fw-bold fs-7">{{ $modeloDocumento->folio_documento }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="bg-light rounded p-3 h-100">
                                    <div class="text-gray-500 fw-bold fs-9 text-uppercase mb-1">Fecha</div>
                                    <div class="text-gray-800 fw-bold fs-7">{{ Carbon::parse($modeloDocumento->au_fechacr)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-100 border border-dashed border-gray-300 rounded p-4 mb-5">
                            <div class="row">
                                <div class="col-6 border-end border-gray-300">
                                    <span class="text-gray-500 fw-bold fs-9 text-uppercase d-block mb-1">De: Remitente Inicial</span>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-25px me-2">
                                            <span class="symbol-label bg-white text-gray-800 fs-8 fw-bold border border-gray-200">
                                                {{ substr($modeloDocumento->areaRemitente->nombre_area ?? 'N', 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="text-gray-800 fw-semibold fs-7 lh-1">{{ $modeloDocumento->areaRemitente->nombre_area ?? 'N/A' }}</span>
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

                        <div class="mb-6">
                            <label class="fw-bold text-gray-600 fs-8 mb-2 text-uppercase">Asunto:</label>
                            <div class="p-3 bg-white border border-gray-200 rounded">
                                <p class="text-gray-800 fs-6 fw-normal m-0 text-break lh-base">
                                    {{ $modeloDocumento->asunto_documento }}
                                </p>
                            </div>
                        </div>

                        @if($modeloDocumento->observacion_documento)
                        <div class="mb-6">
                            <label class="fw-bold text-primary fs-8 mb-2 text-uppercase">
                                <i class="ki-outline ki-note-2 fs-8 text-primary me-1"></i>
                                Nota Inicial / Descripción:
                            </label>
                            <div class="alert alert-dismissible bg-light-primary border border-primary border-dashed p-3 m-0">
                                <div class="fs-7 text-gray-700">{{ $modeloDocumento->observacion_documento }}</div>
                            </div>
                        </div>
                        @endif

                        <div>
                            <h4 class="fw-bold text-gray-800 fs-7 text-uppercase mb-3">Archivos del Documento</h4>
                            @if($archivosOriginales->count() > 0 || $archivosOtros->count() > 0)
                                <div class="row g-3">
                                    {{-- Originales --}}
                                    @foreach($archivosOriginales as $archivo)
                                    <div class="col-12" wire:key="arch-orig-{{ $archivo->id_archivo_documento }}">
                                        <div class="d-flex align-items-center bg-white border border-gray-300 rounded p-2 px-3 shadow-sm">
                                            <i class="ki-outline {{ $archivo->icono ?? 'ki-file' }} fs-2 text-primary me-3"></i>
                                            <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                                                <span class="text-gray-800 fw-bold fs-7 text-truncate" title="{{ $archivo->nombre_original }}">{{ $archivo->nombre_original }}</span>
                                                <span class="text-muted fs-9">Original • {{ $archivo->tamanio_formateado }}</span>
                                            </div>
                                            <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-icon btn-sm btn-light-primary">
                                                <i class="ki-outline ki-eye fs-4"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @endforeach
                                    {{-- Otros --}}
                                    @foreach($archivosOtros as $archivo)
                                    <div class="col-12" wire:key="arch-otro-{{ $archivo->id_archivo_documento }}">
                                        <div class="d-flex align-items-center bg-white border border-gray-300 rounded p-2 px-3 shadow-sm">
                                            <i class="ki-outline {{ $archivo->icono ?? 'ki-file' }} fs-2 text-info me-3"></i>
                                            <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                                                <span class="text-gray-800 fw-bold fs-7 text-truncate" title="{{ $archivo->nombre_original }}">{{ $archivo->nombre_original }}</span>
                                                <span class="text-muted fs-9">Anexo • {{ $archivo->tamanio_formateado }}</span>
                                            </div>
                                            <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-icon btn-sm btn-light-info">
                                                <i class="ki-outline ki-eye fs-4"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted fs-7 fst-italic">Sin archivos adjuntos base.</span>
                            @endif
                        </div>

                    </div>

                    {{-- ========================================== --}}
                    {{-- COLUMNA DERECHA: ESTADOS Y OBSERVACIONES   --}}
                    {{-- ========================================== --}}
                    <div class="col-lg-5 ps-lg-5 border-start-lg border-gray-200">

                        <div class="mb-6 text-center bg-light rounded p-4 border border-dashed border-gray-300">
                            <span class="text-gray-500 fs-8 fw-bold text-uppercase d-block mb-2">Estado Actual</span>
                            @if($modeloDocumento->estado)
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
                                <span class="badge badge-light-{{ $colorEstado }} fs-5 fw-bolder py-3 px-4 shadow-sm">
                                    {{ $modeloDocumento->estado->nombre_estado }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex flex-column gap-4">

                            {{-- 1. CASO: DOCUMENTO OBSERVADO (ALERTA ROJA) --}}
                            {{-- Solo mostramos esto si el estado es Observado, y usamos $motivoObservacion (del movimiento) --}}
                            @if($mostrarAlertaRoja && $motivoObservacion)
                                <div class="notice d-flex bg-light-danger rounded border-start border-4 border-danger p-4 shadow-sm">
                                    <div class="d-flex flex-column w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="text-danger fw-bold m-0">
                                                <i class="ki-outline ki-eye-slash fs-2 text-danger me-2"></i>
                                                Observación
                                            </h5>
                                            @if($fechaObservacion)
                                            <span class="text-danger opacity-75 fs-9">{{ Carbon::parse($fechaObservacion)->format('d/m/Y') }}</span>
                                            @endif
                                        </div>

                                        <div class="fs-7 text-gray-800 bg-white rounded p-3 border border-danger border-dashed">
                                            {{ $motivoObservacion }}
                                        </div>

                                        {{-- Evidencia de Observación --}}
                                        @if($archivosEvidenciaObservacion->count() > 0)
                                            <div class="mt-3">
                                                <span class="text-danger fw-bold fs-9 text-uppercase mb-2 d-block">Evidencia del error:</span>
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($archivosEvidenciaObservacion as $archivo)
                                                    <div class="d-flex align-items-center bg-white border border-danger rounded p-2 shadow-sm">
                                                        <i class="ki-outline {{ $archivo->icono ?? 'ki-file' }} fs-3 text-danger me-2"></i>
                                                        <span class="text-gray-700 fs-8 fw-bold text-truncate flex-grow-1">{{ $archivo->nombre_original }}</span>
                                                        <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-icon btn-xs btn-light-danger">
                                                            <i class="ki-outline ki-eye fs-5"></i>
                                                        </a>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- 2. CASO: RECTIFICACIÓN (ALERTA AMARILLA) --}}
                            @if($motivoRectificacion)
                                <div class="notice d-flex bg-light-warning rounded border-start border-4 border-warning p-4 shadow-sm">
                                    <div class="d-flex flex-column w-100">
                                        <h5 class="text-gray-900 fw-bold mb-2">
                                            <i class="ki-outline ki-information fs-2 text-warning me-2"></i>
                                            Sustento Rectificación
                                        </h5>
                                        <div class="fs-7 text-gray-700 bg-white rounded p-3 border border-warning border-dashed">
                                            {{ $motivoRectificacion }}
                                        </div>

                                        {{-- Evidencia de Rectificación --}}
                                        @if($archivosEvidencia->count() > 0)
                                            <div class="mt-3">
                                                <span class="text-warning fw-bold fs-9 text-uppercase mb-2 d-block">Evidencia Adjunta:</span>
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($archivosEvidencia as $archivo)
                                                    <div class="d-flex align-items-center bg-white border border-warning rounded p-2 shadow-sm">
                                                        <i class="ki-outline {{ $archivo->icono ?? 'ki-file' }} fs-3 text-warning me-2"></i>
                                                        <span class="text-gray-700 fs-8 fw-bold text-truncate flex-grow-1">{{ $archivo->nombre_original }}</span>
                                                        <a href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}" target="_blank" class="btn btn-icon btn-xs btn-light-warning">
                                                            <i class="ki-outline ki-eye fs-5"></i>
                                                        </a>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- 3. CASO: SUBSANACIÓN (ALERTA VERDE) --}}
                            @if($mostrarSubsanacion)
                                <div class="notice d-flex bg-light-success rounded border-start border-4 border-success p-4 shadow-sm">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-success fw-bold mb-1">
                                            <i class="ki-outline ki-check-circle fs-2 text-success me-2"></i>
                                            Subsanado
                                        </h5>
                                        <div class="fs-7 text-gray-600">
                                            La observación ha sido corregida. El documento está limpio.
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- ESTADO LIMPIO (Sin alertas activas) --}}
                            @if(!$motivoRectificacion && !($mostrarAlertaRoja && $motivoObservacion) && !$mostrarSubsanacion)
                                <div class="text-center py-5">
                                    <i class="ki-outline ki-shield-tick fs-3x text-gray-300 mb-3"></i>
                                    <div class="text-gray-400 fs-7 fw-bold">Sin incidencias ni observaciones</div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                @endif {{-- End if modeloDocumento --}}
            </div>

            <div class="modal-footer border-0 pt-0 justify-content-end">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
