<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta tu Trámite - SGD</title>

    <link rel="shortcut icon" href="{{ asset('/assets/media/logo-unia.webp') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --kt-primary: #009ef7;
            --kt-text-gray-800: #181c32;
            --kt-text-gray-600: #7e8299;
            --kt-text-gray-500: #a1a5b7;
            --kt-bg-light: #f5f8fa;
        }
        body { font-family: 'Inter', Helvetica, sans-serif; background-color: var(--kt-bg-light); color: var(--kt-text-gray-600); display: flex; flex-direction: column; min-height: 100vh; }

        /* Header & Cards */
        .header-brand { background: #ffffff; box-shadow: 0px 10px 30px 0px rgba(82, 63, 105, 0.05); padding: 15px 0; margin-bottom: 30px; }
        .card { border: 0; box-shadow: 0px 0px 20px 0px rgba(76, 87, 125, 0.02); border-radius: 0.75rem; background-color: #ffffff; margin-bottom: 20px; }
        .card-header { border-bottom: 1px solid #eff2f5; padding: 1.5rem 2rem; min-height: 60px; background: transparent; }
        .card-body { padding: 2rem; }

        /* Search Input */
        .form-control-search { background-color: #f5f8fa; border: 1px solid transparent; border-radius: 0.475rem; padding: 1rem 1rem 1rem 3rem; font-size: 1.1rem; font-weight: 500; transition: all 0.2s ease; }
        .form-control-search:focus { background-color: #fff; border-color: var(--kt-primary); box-shadow: none; }
        .search-icon { position: absolute; top: 50%; left: 1.2rem; transform: translateY(-50%); color: var(--kt-text-gray-500); font-size: 1.3rem; }

        /* TIMELINE MEJORADO */
        .timeline-container { position: relative; }
        /* Línea conectora */
        .timeline-track { position: absolute; top: 20px; bottom: 20px; left: 22px; width: 2px; background-color: #e4e6ef; z-index: 0; }

        .timeline-item { position: relative; display: flex; gap: 1.25rem; margin-bottom: 2rem; z-index: 1; }
        .timeline-item:last-child { margin-bottom: 0; }

        .timeline-icon {
            flex-shrink: 0; width: 46px; height: 46px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #fff; border: 2px solid transparent;
            box-shadow: 0 0 0 5px #fff; transition: all 0.3s;
            z-index: 2; /* Encima de la línea */
        }

        /* Variantes de Iconos (Colores) */
        .timeline-icon.success { border-color: #50cd89; color: #50cd89; background-color: #e8fff3; }
        .timeline-icon.primary { border-color: #009ef7; color: #009ef7; background-color: #f1faff; }
        .timeline-icon.warning { border-color: #ffc700; color: #ffc700; background-color: #fff8dd; }
        .timeline-icon.danger { border-color: #f1416c; color: #f1416c; background-color: #fff5f8; }
        .timeline-icon.info { border-color: #7239ea; color: #7239ea; background-color: #f8f5ff; }
        /* Variante Gris (Secondary) para estados neutros */
        .timeline-icon.secondary { border-color: #e4e6ef; color: #7e8299; background-color: #f9f9f9; }

        .timeline-content { flex-grow: 1; }

        /* Animación Slide */
        .timeline-hidden { display: none; }
        .timeline-hidden.show { display: block; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Utilidades Badge */
        .bg-light-secondary { background-color: #f5f8fa; color: #7e8299; }
        .bg-light-success { background-color: #e8fff3; color: #50cd89; }
        .bg-light-danger { background-color: #fff5f8; color: #f1416c; }
        .bg-light-primary { background-color: #f1faff; color: #009ef7; }
        .bg-light-warning { background-color: #fff8dd; color: #ffc700; }
        .bg-light-info { background-color: #f8f5ff; color: #7239ea; }

        .footer { margin-top: auto; padding: 20px; text-align: center; font-size: 0.85rem; color: var(--kt-text-gray-500); }
    </style>
</head>
<body>

    <div class="header-brand">
        <div class="container d-flex align-items-center gap-3">
            <a href="{{ route('inicio.index') }}">
                <img alt="Logo" src="{{ asset('assets/media/logo-unia.webp') }}" style="height: 45px;" />
            </a>
            <div class="d-flex flex-column border-start ps-3 ms-1">
                <h1 class="fs-5 fw-bold text-gray-800 mb-0 lh-1">SGD</h1>
                <span class="fs-8 text-gray-400 fw-medium">Consulta Pública</span>
            </div>
        </div>
    </div>

    <div class="container mb-5">

        <div class="card border-0 mb-6">
            <div class="card-body py-8 text-center">
                <h2 class="fs-2 fw-bold text-gray-800 mb-2">Consulta tu Trámite</h2>
                <div class="text-gray-500 fs-6 fw-normal mb-6">Ingresa el código del expediente para ver su estado actual</div>

                <form method="post" action="{{ route('consulta.buscar') }}" class="mx-auto mw-600px position-relative" id="formConsulta">
                    @csrf
                    <i class="bi bi-search search-icon"></i>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-search @error('expediente') is-invalid @enderror"
                               name="expediente"
                               value="{{ old('expediente', $expediente ?? '') }}"
                               placeholder="Ej: EXP-001-2025" required>
                        <button type="submit" class="btn btn-primary fw-semibold px-4" id="btnBuscar">
                            <span id="btnText">Buscar</span>
                            <span id="btnSpinner" class="d-none">
                                <span class="spinner-border spinner-border-sm align-middle"></span>
                            </span>
                        </button>
                    </div>
                    @error('expediente')
                    <div class="text-danger fw-medium fs-7 mt-2 text-start ps-2">{{ $message }}</div>
                    @enderror
                </form>
            </div>
        </div>

        <div id="loadingSkeleton" class="w-100 placeholder-glow d-none">
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <span class="placeholder col-6 mb-4" style="height: 20px; border-radius: 5px;"></span>
                            <span class="placeholder col-12 mb-2" style="height: 15px; border-radius: 5px;"></span>
                            <span class="placeholder col-10" style="height: 15px; border-radius: 5px;"></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-body">
                            <span class="placeholder col-5 mb-4" style="height: 20px; border-radius: 5px;"></span>
                            <span class="placeholder col-12 mb-3" style="height: 40px; border-radius: 5px;"></span>
                            <span class="placeholder col-12 mb-3" style="height: 40px; border-radius: 5px;"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="resultadosContainer">
            @if(isset($resultado) && $resultado)
            <div class="row g-5">

                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-3 pb-4">
                            <div class="flex-grow-1">
                                <h3 class="card-title fw-bold fs-3 text-gray-800 mb-1">Detalle del Expediente</h3>
                                @php
                                $estadoNombre = strtoupper(optional($resultado->estado)->nombre_estado);
                                $badgeClass = match($estadoNombre) {
                                    'RECEPCIONADO', 'FINALIZADO', 'SUBSANADO', 'RECEPCION SUBSANADA' => 'bg-light-success text-success',
                                    'OBSERVADO', 'ANULADO', 'RECHAZAR RECTIFICACION' => 'bg-light-danger text-danger',
                                    'EN TRAMITE', 'DERIVADO' => 'bg-light-primary text-primary',
                                    'ARCHIVADO' => 'bg-light-info text-info',
                                    default => 'bg-light-secondary text-gray-600'
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }} fs-7 fw-bold px-3 py-2 mt-1">
                                    <i class="bi bi-circle-fill fs-9 me-1"></i>{{ $estadoNombre }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body pt-3">
                            {{-- N° Expediente y Documento --}}
                            <div class="row g-3 mb-5">
                                <div class="col-sm-6">
                                    <div class="bg-light-primary border border-primary border-dashed rounded p-4 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-file-earmark-text text-primary fs-2 me-2"></i>
                                            <span class="text-gray-600 fw-semibold fs-8 text-uppercase">N° Expediente</span>
                                        </div>
                                        <div class="text-primary fw-bolder fs-2">{{ $resultado->expediente_documento }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="bg-light rounded border border-gray-200 p-4 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-hash text-gray-600 fs-2 me-2"></i>
                                            <span class="text-gray-600 fw-semibold fs-8 text-uppercase">N° Documento</span>
                                        </div>
                                        <div class="text-gray-800 fw-bold fs-3">{{ $resultado->numero_documento }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Asunto --}}
                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-clipboard-data text-gray-600 fs-3 me-2"></i>
                                    <label class="text-gray-700 fw-bold fs-6 mb-0 text-uppercase">Asunto</label>
                                </div>
                                <div class="p-4 bg-light rounded border border-gray-200">
                                    <p class="fs-6 text-gray-800 fw-normal lh-base mb-0">
                                        {{ $resultado->asunto_documento }}
                                    </p>
                                </div>
                            </div>

                            <div class="separator separator-dashed border-gray-300 my-6"></div>

                            {{-- Remitente y Ubicación --}}
                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="symbol symbol-50px me-3">
                                            <div class="symbol-label bg-light-primary">
                                                <i class="bi bi-person-badge fs-2 text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">Remitente</div>
                                            <div class="text-gray-800 fw-bold fs-6 mb-1">{{ optional($resultado->areaRemitente)->nombre_area }}</div>
                                            <div class="d-flex align-items-center text-gray-500 fs-7">
                                                <i class="bi bi-calendar-event me-1 fs-8"></i>
                                                {{ $resultado->fecha_emision_documento ? \Carbon\Carbon::parse($resultado->fecha_emision_documento)->format('d M Y') : '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="symbol symbol-50px me-3">
                                            <div class="symbol-label bg-light-success">
                                                <i class="bi bi-geo-alt-fill fs-2 text-success"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">Ubicación Actual</div>
                                            <div class="text-gray-800 fw-bold fs-6 mb-1">{{ optional($resultado->areaDestino)->nombre_area }}</div>
                                            <div class="d-flex align-items-center">
                                                @if(in_array($estadoNombre, ['ARCHIVADO', 'FINALIZADO', 'ANULADO']))
                                                    <span class="badge badge-light-secondary fs-8">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Trámite Finalizado
                                                    </span>
                                                @else
                                                    <span class="badge badge-light-success fs-8">
                                                        <i class="bi bi-arrow-repeat me-1"></i>En proceso
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header border-0 pb-3 pt-6">
                            <h3 class="card-title fw-bold text-gray-800 fs-3 d-flex align-items-center">
                                <i class="bi bi-clock-history text-primary fs-2 me-2"></i>
                                Seguimiento
                            </h3>
                        </div>
                        <div class="card-body pt-2">
                            <div class="timeline-container position-relative">
                                <div class="timeline-track"></div>

                                @php
                                    // Obtenemos TODOS los movimientos
                                    $movimientos = $resultado->movimientos()->orderByDesc('au_fechacr')->get();

                                    // Separamos: los primeros 5 (visibles) y el resto (ocultos)
                                    $visibleMovements = $movimientos->take(5);
                                    $hiddenMovements = $movimientos->skip(5);
                                @endphp

                                {{-- A. ELEMENTOS VISIBLES --}}
                                @foreach($visibleMovements as $mov)
                                    @php
                                        $movEstado = strtoupper(optional($mov->estado)->nombre_estado);
                                        // Definición exacta de iconos y colores
                                        $style = match($movEstado) {
                                            'RECEPCIONADO', 'FINALIZADO', 'SUBSANADO', 'RECEPCION SUBSANADA' => ['class' => 'success', 'icon' => 'bi-check-circle-fill'],
                                            'DERIVADO' => ['class' => 'primary', 'icon' => 'bi-send-fill'],
                                            'ARCHIVADO' => ['class' => 'info', 'icon' => 'bi-archive-fill'],
                                            'OBSERVADO', 'RECHAZAR RECTIFICACION' => ['class' => 'danger', 'icon' => 'bi-exclamation-circle-fill'],
                                            'POR RECTIFICAR', 'SOLICITAR RECTIFICACION' => ['class' => 'warning', 'icon' => 'bi-pencil-square'],
                                            // ESTADOS GRISES / NEUTROS (Para "Observacion Recepcionado" o estados intermedios)
                                            'OBSERVACION RECEPCIONADO', 'EN TRAMITE' => ['class' => 'secondary', 'icon' => 'bi-circle-fill fs-7'],
                                            default => ['class' => 'secondary', 'icon' => 'bi-circle-fill fs-7']
                                        };
                                    @endphp
                                    <div class="timeline-item">
                                        <div class="timeline-icon {{ $style['class'] }}">
                                            <i class="bi {{ $style['icon'] }}"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="flex-grow-1 pe-2">
                                                        @if($mov->observacion_doc_movimiento)
                                                            <a href="#" class="text-gray-900 text-hover-primary fw-bold fs-6 text-decoration-none"
                                                               data-bs-toggle="modal"
                                                               data-bs-target="#modal-detalle-movimiento"
                                                               data-motivo="{{ $mov->observacion_doc_movimiento }}"
                                                               data-estado="{{ $movEstado }}"
                                                               data-fecha="{{ $mov->au_fechacr }}">
                                                               {{ $movEstado }}
                                                               <i class="bi bi-info-circle-fill ms-1 text-primary fs-7"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-gray-900 fw-bold fs-6">{{ $movEstado }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="badge badge-light-primary fs-8 fw-semibold flex-shrink-0">
                                                         {{ $mov->au_fechacr ? \Carbon\Carbon::parse($mov->au_fechacr)->diffForHumans(null, true, true) : '' }}
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-column gap-1">
                                                    <div class="text-gray-600 fs-7 d-flex align-items-center">
                                                        <i class="bi bi-calendar3 me-2 fs-7 text-gray-500"></i>
                                                        <span class="fw-medium">{{ $mov->au_fechacr ? \Carbon\Carbon::parse($mov->au_fechacr)->format('d/m/Y h:i A') : '-' }}</span>
                                                    </div>
                                                    <div class="text-gray-600 fs-7 d-flex align-items-center">
                                                        <i class="bi bi-building me-2 fs-7 text-gray-500"></i>
                                                        <span class="fw-semibold text-gray-700">
                                                            {{ optional($mov->areaDestino)->nombre_area ?? optional($mov->areaOrigen)->nombre_area }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- B. ELEMENTOS OCULTOS (SLIDE) --}}
                                @if($hiddenMovements->count() > 0)
                                    <div id="timeline-hidden" class="timeline-hidden">
                                        @foreach($hiddenMovements as $mov)
                                            @php
                                                $movEstado = strtoupper(optional($mov->estado)->nombre_estado);
                                                $style = match($movEstado) {
                                                    'RECEPCIONADO', 'FINALIZADO', 'SUBSANADO', 'RECEPCION SUBSANADA' => ['class' => 'success', 'icon' => 'bi-check-circle-fill'],
                                                    'DERIVADO' => ['class' => 'primary', 'icon' => 'bi-send-fill'],
                                                    'ARCHIVADO' => ['class' => 'info', 'icon' => 'bi-archive-fill'],
                                                    'OBSERVADO', 'RECHAZAR RECTIFICACION' => ['class' => 'danger', 'icon' => 'bi-exclamation-circle-fill'],
                                                    'POR RECTIFICAR', 'SOLICITAR RECTIFICACION' => ['class' => 'warning', 'icon' => 'bi-pencil-square'],
                                                    'OBSERVACION RECEPCIONADO', 'EN TRAMITE' => ['class' => 'secondary', 'icon' => 'bi-circle-fill fs-7'],
                                                    default => ['class' => 'secondary', 'icon' => 'bi-circle-fill fs-7']
                                                };
                                            @endphp
                                            <div class="timeline-item">
                                                <div class="timeline-icon {{ $style['class'] }}">
                                                    <i class="bi {{ $style['icon'] }}"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="d-flex flex-column">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div class="flex-grow-1 pe-2">
                                                                @if($mov->observacion_doc_movimiento)
                                                                    <a href="#" class="text-gray-900 text-hover-primary fw-bold fs-6 text-decoration-none"
                                                                       data-bs-toggle="modal"
                                                                       data-bs-target="#modal-detalle-movimiento"
                                                                       data-motivo="{{ $mov->observacion_doc_movimiento }}"
                                                                       data-estado="{{ $movEstado }}"
                                                                       data-fecha="{{ $mov->au_fechacr }}">
                                                                       {{ $movEstado }}
                                                                       <i class="bi bi-info-circle-fill ms-1 text-primary fs-7"></i>
                                                                    </a>
                                                                @else
                                                                    <span class="text-gray-900 fw-bold fs-6">{{ $movEstado }}</span>
                                                                @endif
                                                            </div>
                                                            <span class="badge badge-light-primary fs-8 fw-semibold flex-shrink-0">
                                                                 {{ $mov->au_fechacr ? \Carbon\Carbon::parse($mov->au_fechacr)->diffForHumans(null, true, true) : '' }}
                                                            </span>
                                                        </div>
                                                        <div class="d-flex flex-column gap-1">
                                                            <div class="text-gray-600 fs-7 d-flex align-items-center">
                                                                <i class="bi bi-calendar3 me-2 fs-7 text-gray-500"></i>
                                                                <span class="fw-medium">{{ $mov->au_fechacr ? \Carbon\Carbon::parse($mov->au_fechacr)->format('d/m/Y h:i A') : '-' }}</span>
                                                            </div>
                                                            <div class="text-gray-600 fs-7 d-flex align-items-center">
                                                                <i class="bi bi-building me-2 fs-7 text-gray-500"></i>
                                                                <span class="fw-semibold text-gray-700">
                                                                     {{ optional($mov->areaDestino)->nombre_area ?? optional($mov->areaOrigen)->nombre_area }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="text-center mt-5 pt-2 position-relative" style="z-index: 2; background: #fff;">
                                        <button type="button" class="btn btn-sm btn-light-primary btn-active-primary fw-bold shadow-sm" id="btnVerMas" onclick="toggleTimeline()">
                                            <span class="indicator-label">
                                                <i class="bi bi-chevron-down fs-5 align-middle me-1"></i>
                                                Ver historial completo ({{ $hiddenMovements->count() }})
                                            </span>
                                            <span class="indicator-active d-none">
                                                <i class="bi bi-chevron-up fs-5 align-middle me-1"></i>
                                                Ocultar historial
                                            </span>
                                        </button>
                                    </div>
                                @endif

                                @if($movimientos->isEmpty())
                                    <div class="text-center py-10">
                                        <i class="bi bi-inbox text-gray-400 fs-3x mb-3 d-block"></i>
                                        <span class="text-gray-500 fw-medium fs-6">Sin movimientos registrados</span>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @elseif(isset($resultado) && !$resultado)
            <div class="card border-dashed border-gray-300 bg-light-primary">
                <div class="card-body text-center py-10">
                    <i class="bi bi-search fs-3x text-primary mb-4"></i>
                    <h2 class="fs-2 fw-bold text-gray-800 mb-2">Expediente no encontrado</h2>
                    <p class="text-gray-600 fs-6 mb-0">El código <strong>"{{ $expediente ?? '' }}"</strong> no existe. Verifica e intenta nuevamente.</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <p class="mb-0">© {{ date('Y') }} Universidad Nacional Intercultural de la Amazonía</p>
    </div>

    {{-- =================================================================== --}}
    {{-- MODALES INTEGRADOS (SIN ERROR DE INCLUDE)                           --}}
    {{-- =================================================================== --}}

    {{-- MODAL RECTIFICAR --}}
    @if(isset($resultado) && $resultado)
    <div class="modal fade" id="modal-rectificar-publico" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Solicitud de Rectificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('consulta.rectificar') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="expediente" value="{{ $resultado->expediente_documento }}">
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle fs-3 me-3"></i>
                            <div>Tu solicitud será revisada por Mesa de Partes. Describe claramente el motivo.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold required">Motivo de la rectificación</label>
                            <textarea name="motivo" class="form-control" rows="4" required>{{ old('motivo') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Archivos de evidencia (opcional)</label>
                            <input type="file" name="archivos_evidencia[]" class="form-control" multiple accept=".pdf,.png,.jpg,.jpeg">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Enviar solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DETALLE MOVIMIENTO --}}
    <div class="modal fade" id="modal-detalle-movimiento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detalle del Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="fw-bold text-gray-600 mb-1 d-block">Estado</label>
                        <span class="badge bg-primary fs-6" id="modal-estado"></span>
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold text-gray-600 mb-1 d-block">Fecha</label>
                        <span class="text-gray-800 fs-6" id="modal-fecha"></span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-gray-600 mb-1 d-block">Motivo / Observación</label>
                        <div class="p-3 bg-light rounded text-break" id="modal-motivo" style="min-height: 80px; white-space: pre-wrap;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle Timeline Slide (Efecto Ver Más)
        function toggleTimeline() {
            const hiddenContainer = document.getElementById('timeline-hidden');
            const btn = document.getElementById('btnVerMas');
            const label = btn.querySelector('.indicator-label');
            const active = btn.querySelector('.indicator-active');

            if (hiddenContainer.style.display === 'block') {
                hiddenContainer.classList.remove('show');
                setTimeout(() => { hiddenContainer.style.display = 'none'; }, 400); // Esperar animación CSS
                label.classList.remove('d-none');
                active.classList.add('d-none');
            } else {
                hiddenContainer.style.display = 'block';
                // Pequeño timeout para permitir que el display:block se renderice antes de agregar la clase de animación
                setTimeout(() => { hiddenContainer.classList.add('show'); }, 10);
                label.classList.add('d-none');
                active.classList.remove('d-none');
            }
        }

        // SKELETON LOADING
        document.getElementById('formConsulta').addEventListener('submit', function() {
            const res = document.getElementById('resultadosContainer');
            if(res) res.classList.add('d-none');
            document.getElementById('loadingSkeleton').classList.remove('d-none');
            document.getElementById('btnText').classList.add('d-none');
            document.getElementById('btnSpinner').classList.remove('d-none');
            document.getElementById('btnBuscar').setAttribute('disabled', 'disabled');
        });

        // LÓGICA MODAL DETALLE
        const detalleMovModal = document.getElementById('modal-detalle-movimiento');
        if (detalleMovModal) {
            detalleMovModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const motivo = button.dataset.motivo;
                const estado = button.dataset.estado;
                const fecha = button.dataset.fecha;
                document.getElementById('modal-motivo').textContent = motivo;
                document.getElementById('modal-estado').textContent = estado;
                document.getElementById('modal-fecha').textContent = fecha;
            });
        }
    </script>

    {{-- ALERTAS DE SESIÓN --}}
    @if(session('status'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Solicitud enviada!',
            text: "{{ session('status') }}",
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#009ef7'
        });
    </script>
    @endif

    @if($errors->has('motivo'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ $errors->first('motivo') }}",
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#f1416c'
        });
    </script>
    @endif
</body>
</html>
