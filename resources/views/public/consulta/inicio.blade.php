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
            --kt-primary-light: #f1faff;
            --kt-text-gray-800: #181c32;
            --kt-text-gray-600: #7e8299;
            --kt-text-gray-500: #a1a5b7;
            --kt-text-gray-400: #b5b5c3;
            --kt-bg-light: #f5f8fa;
            --kt-card-shadow: 0px 0px 20px 0px rgba(76, 87, 125, 0.02);
        }

        body {
            font-family: 'Inter', Helvetica, sans-serif;
            background-color: var(--kt-bg-light);
            color: var(--kt-text-gray-600);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* --- Header y Footer --- */
        .header-brand {
            background: #ffffff;
            box-shadow: 0px 10px 30px 0px rgba(82, 63, 105, 0.05);
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .footer {
            margin-top: auto;
            padding: 20px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--kt-text-gray-500);
        }

        /* --- Cards --- */
        .card {
            border: 0;
            box-shadow: var(--kt-card-shadow);
            border-radius: 0.75rem;
            background-color: #ffffff;
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eff2f5;
            padding: 1.5rem 2rem;
            min-height: 70px;
            background: transparent;
        }

        .card-body {
            padding: 2rem;
        }

        /* --- Buscador (Estilo Metronic Input) --- */
        .form-control-search {
            background-color: #f5f8fa;
            border: 1px solid transparent;
            color: var(--kt-text-gray-800);
            border-radius: 0.475rem;
            padding: 1rem 1rem 1rem 3rem;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control-search:focus {
            background-color: #f5f8fa;
            border-color: #eef3f7;
            color: var(--kt-text-gray-800);
            box-shadow: none;
        }

        .search-icon {
            position: absolute;
            top: 50%;
            left: 1.2rem;
            transform: translateY(-50%);
            color: var(--kt-text-gray-500);
            font-size: 1.3rem;
        }

        /* --- Botones --- */
        .btn-primary {
            background-color: var(--kt-primary);
            border-color: var(--kt-primary);
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            border-radius: 0.475rem;
        }

        .btn-primary:hover {
            background-color: #0095e8;
        }

        .btn-warning {
            background-color: #ffc700;
            border-color: #ffc700;
            color: #ffffff;
            font-weight: 600;
        }
        .btn-warning:hover {
            background-color: #f1bc00;
            border-color: #f1bc00;
        }

        /* --- DETALLES (Mejorado) --- */
        .info-box {
            background-color: #f9f9f9;
            border: 1px dashed #e4e6ef;
            border-radius: 0.475rem;
            padding: 1.25rem;
            height: 100%;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: all 0.2s;
        }

        .info-box:hover {
            background-color: #f1faff;
            border-color: var(--kt-primary);
        }

        .symbol-circle {
            width: 45px;
            height: 45px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }

        /* --- TIMELINE (Mejorado) --- */
        .timeline-label {
            position: relative;
            padding-left: 0;
        }

        .timeline-item {
            display: flex;
            flex-direction: row;
            position: relative;
            margin-bottom: 1.75rem;
        }

        /* Línea conectora */
        .timeline-item:before {
            content: "";
            position: absolute;
            left: 21px;
            top: 25px;
            bottom: -25px;
            width: 2px;
            background-color: #e4e6ef;
            z-index: 0;
        }

        .timeline-item:last-child:before {
            display: none;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        /* Badge circular */
        .timeline-badge {
            flex-shrink: 0;
            background-color: #ffffff;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #e4e6ef;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            position: relative;
            margin-right: 1.25rem;
        }

        .timeline-badge i {
            font-size: 1.2rem;
            color: var(--kt-text-gray-500);
        }

        /* Estados del Timeline */
        .timeline-badge.success { border-color: #50cd89; }
        .timeline-badge.success i { color: #50cd89; }

        .timeline-badge.primary { border-color: var(--kt-primary); }
        .timeline-badge.primary i { color: var(--kt-primary); }

        .timeline-badge.info { border-color: #7239ea; }
        .timeline-badge.info i { color: #7239ea; }

        .timeline-badge.warning { border-color: #ffc700; }
        .timeline-badge.warning i { color: #ffc700; }

        .timeline-badge.danger { border-color: #f1416c; }
        .timeline-badge.danger i { color: #f1416c; }


        .timeline-content {
            flex-grow: 1;
            background-color: #f9f9f9;
            padding: 1rem;
            border-radius: 0.475rem;
        }

        /* --- Badges Generales --- */
        .badge {
            padding: 0.5rem 0.6rem;
            border-radius: 0.325rem;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge-light-primary { background-color: #f1faff; color: #009ef7; }
        .badge-light-success { background-color: #e8fff3; color: #50cd89; }
        .badge-light-warning { background-color: #fff8dd; color: #ffc700; }
        .badge-light-danger { background-color: #fff5f8; color: #f1416c; }
        .badge-light-secondary { background-color: #f5f8fa; color: #7e8299; }
        .badge-light-info { background-color: #f8f5ff; color: #7239ea; }

    </style>
</head>
<body>

    <div class="header-brand">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('inicio.index') }}" class="m-auto">
                    <img alt="Logo" src="{{ asset('assets/media/logo-unia.webp') }}" style="height: 50px;" />
                </a>
                <div class="d-flex flex-column">
                    <h1 class="fs-4 fw-bold text-gray-800 mb-0">SGD</h1>
                    <span class="fs-7 text-gray-400 fw-bold">Gestión Documentaria</span>
                </div>
            </div>
            <div>
                <a href="/" class="btn btn-sm btn-light text-gray-600 fw-bold">Ir al Inicio</a>
            </div>
        </div>
    </div>

    <div class="container mb-5">

        <div class="card border-0 mb-8">
            <div class="card-body py-10">
                <div class="text-center mb-8">
                    <h2 class="fs-1 fw-bold text-gray-800 mb-2">Consulta tu Trámite</h2>
                    <div class="text-gray-500 fs-5 fw-semibold">Ingresa el código del expediente para rastrear su ubicación</div>
                </div>

                <form method="post" action="{{ route('consulta.buscar') }}" class="mx-auto mw-700px position-relative">
                    @csrf
                    <i class="bi bi-search search-icon"></i>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-search @error('expediente') is-invalid @enderror" name="expediente" value="{{ old('expediente', $expediente ?? '') }}" placeholder="Ej: EXP-001-2025" required>
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                    @error('expediente')
                    <div class="text-danger fw-bold fs-7 mt-2 ps-1">{{ $message }}</div>
                    @enderror
                </form>
            </div>
        </div>

        @if(isset($resultado) && $resultado)
        <div class="row g-6">

            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header border-0 pb-0">
                        <div class="card-title m-0 d-flex flex-column"> <span class="card-label fw-bold fs-2 text-gray-800">Detalle del Expediente</span>
                            <span class="text-gray-400 fw-semibold fs-6 mt-1">Información general del trámite</span>
                        </div>

                        @php
                        $estadoNombre = strtoupper(optional($resultado->estado)->nombre_estado);
                        $badgeClass = match($estadoNombre) {
                            'RECEPCIONADO', 'FINALIZADO' => 'badge-light-success',
                            'OBSERVADO', 'ANULADO', 'RECHAZAR RECTIFICACION' => 'badge-light-danger',
                            'EN TRAMITE', 'DERIVADO' => 'badge-light-primary',
                            'ARCHIVADO' => 'badge-light-info',
                            'POR RECTIFICAR', 'SOLICITAR RECTIFICACION' => 'badge-light-warning',
                            default => 'badge-light-warning'
                        };
                        @endphp
                        <div class="card-toolbar">
                            <span class="badge {{ $badgeClass }} fs-6 px-3 py-2">{{ $estadoNombre }}</span>
                        </div>
                    </div>

                    <div class="card-body pt-6">
                        <div class="d-flex flex-wrap gap-5 mb-8">
                            <div class="flex-grow-1 border border-gray-200 rounded p-4 bg-light">
                                <div class="text-gray-500 fw-bold fs-7 mb-1">NÚMERO DE EXPEDIENTE</div>
                                <div class="text-primary fw-bold fs-3">{{ $resultado->expediente_documento }}</div>
                            </div>
                            <div class="flex-grow-1 border border-gray-200 rounded p-4 bg-light">
                                <div class="text-gray-500 fw-bold fs-7 mb-1">N° DOCUMENTO</div>
                                <div class="text-gray-800 fw-bold fs-4">{{ $resultado->numero_documento }}</div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <label class="text-gray-500 fw-bold fs-7 mb-2">ASUNTO DEL TRÁMITE</label>
                            <div class="bg-light-primary rounded p-4 border border-primary border-opacity-10">
                                <p class="text-gray-800 fw-semibold fs-6 mb-0 lh-base">
                                    {{ $resultado->asunto_documento }}
                                </p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="symbol-circle text-primary">
                                        <i class="bi bi-building fs-3"></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-500 fw-bold fs-7">Origen (Remitente)</span>
                                        <span class="text-gray-800 fw-bold fs-6">{{ optional($resultado->areaRemitente)->nombre_area }}</span>
                                        <span class="text-gray-400 fs-8">{{ $resultado->fecha_emision_documento ? \Carbon\Carbon::parse($resultado->fecha_emision_documento)->format('d M Y') : '' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="symbol-circle text-danger">
                                        <i class="bi bi-geo-alt-fill fs-3"></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-500 fw-bold fs-7">Ubicación Actual</span>
                                        <span class="text-gray-800 fw-bold fs-6">{{ optional($resultado->areaDestino)->nombre_area }}</span>

                                        @if(in_array($estadoNombre, ['ARCHIVADO', 'FINALIZADO', 'ANULADO']))
                                        <span class="text-gray-500 fw-bold fs-8">
                                            <i class="bi bi-check-all me-1"></i> Trámite Concluido
                                        </span>
                                        @else
                                        <span class="text-success fw-bold fs-8">
                                            <i class="bi bi-hourglass-split me-1"></i> En proceso
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($estadoNombre === 'ARCHIVADO')
                        <div class="border rounded p-4 bg-light mt-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="symbol-circle text-warning me-3">
                                        <i class="bi bi-pencil-square fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-gray-800">Solicitar rectificación</div>
                                        <div class="text-gray-500 fs-7">Envía tu observación a Mesa de Partes</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-rectificar-publico">
                                    <i class="bi bi-pencil me-1"></i> Rectificar
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title fw-bold text-gray-800">Seguimiento</h3>
                    </div>
                    <div class="card-body pt-6">
                        <div class="timeline-label">

                            @forelse($resultado->movimientos()->orderByDesc('au_fechacr')->limit(10)->get() as $mov)
                            @php
                                $movEstado = strtoupper(optional($mov->estado)->nombre_estado);
                                $isFirst = $loop->first;

                                // 1. COLORES PARA LOS ESTADOS
                                $colorClass = match($movEstado) {
                                    'RECEPCIONADO', 'FINALIZADO' => 'success',
                                    'DERIVADO' => 'primary',
                                    'ARCHIVADO' => 'info',
                                    'OBSERVADO', 'RECHAZAR RECTIFICACION', 'RECHAZAR RECTIFICACIÓN' => 'danger',
                                    'POR RECTIFICAR', 'SOLICITAR RECTIFICACION', 'SOLICITAR RECTIFICACIÓN' => 'warning',
                                    default => 'secondary' // Gris por defecto
                                };

                                // 2. ICONOS PARA LOS ESTADOS
                                $iconClass = match($movEstado) {
                                    'RECEPCIONADO', 'FINALIZADO' => 'bi-check-lg',
                                    'DERIVADO' => 'bi-send',
                                    'ARCHIVADO' => 'bi-archive',
                                    'OBSERVADO' => 'bi-exclamation-lg',
                                    'POR RECTIFICAR' => 'bi-pencil-square',
                                    'SOLICITAR RECTIFICACION', 'SOLICITAR RECTIFICACIÓN' => 'bi-file-earmark-text',
                                    'RECHAZAR RECTIFICACION', 'RECHAZAR RECTIFICACIÓN' => 'bi-x-circle',
                                    default => 'bi-circle-fill'
                                };
                            @endphp

                            <div class="timeline-item">
                                <div class="timeline-badge {{ $colorClass }} {{ $isFirst ? 'bg-light-'.$colorClass : '' }}">
                                    <i class="bi {{ $iconClass }}"></i>
                                </div>

                                <div class="timeline-content">
                                    <div class="d-flex flex-stack mb-1">
                                        @if($mov->observacion_doc_movimiento)
                                            <span
                                                class="text-gray-800 fw-bold fs-6 cursor-pointer text-decoration-underline"
                                                style="cursor: pointer;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-detalle-movimiento"
                                                data-motivo="{{ $mov->observacion_doc_movimiento }}"
                                                data-estado="{{ optional($mov->estado)->nombre_estado }}"
                                                data-fecha="{{ $mov->au_fechacr ? \Carbon\Carbon::parse($mov->au_fechacr)->format('d/m/Y h:i A') : '' }}">
                                                {{ optional($mov->estado)->nombre_estado }}
                                            </span>
                                        @else
                                            <span class="text-gray-800 fw-bold fs-6">{{ optional($mov->estado)->nombre_estado }}</span>
                                        @endif

                                        <span class="text-gray-400 fs-8 text-end">
                                            {{ $mov->au_fechacr ? \Carbon\Carbon::parse($mov->au_fechacr)->diffForHumans() : '' }}
                                        </span>
                                    </div>

                                    <div class="d-flex flex-column text-gray-600 fs-7">
                                        <span>
                                            <i class="bi bi-clock me-1 fs-8"></i>
                                            {{ $mov->au_fechacr ? \Carbon\Carbon::parse($mov->au_fechacr)->format('d/m/Y h:i A') : '' }}
                                        </span>
                                        @php
                                            $areaMovimiento = optional($mov->areaDestino)->nombre_area ?? optional($mov->areaOrigen)->nombre_area;
                                        @endphp
                                        @if(!empty($areaMovimiento))
                                        <span>
                                            <i class="bi bi-building me-1 fs-8"></i>
                                            Área: <span class="fw-semibold text-gray-800">{{ $areaMovimiento }}</span>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <span class="text-gray-400">Sin movimientos registrados</span>
                            </div>
                            @endforelse

                            @php
                                $primerMovimiento = $resultado->movimientos()->orderBy('au_fechacr', 'asc')->first();
                            @endphp
                            @if($primerMovimiento && strtoupper(optional($primerMovimiento->estado)->nombre_estado) === 'RECEPCIONADO')
                            <div class="timeline-item">
                                <div class="timeline-badge primary">
                                    <i class="bi bi-send"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex flex-stack mb-1">
                                        <span class="text-gray-800 fw-bold fs-6">DERIVADO</span>
                                        <span class="text-gray-400 fs-8 text-end">
                                            {{ $primerMovimiento->au_fechacr ? \Carbon\Carbon::parse($primerMovimiento->au_fechacr)->diffForHumans() : '' }}
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column text-gray-600 fs-7">
                                        <span>
                                            <i class="bi bi-clock me-1 fs-8"></i>
                                            {{ $primerMovimiento->au_fechacr ? \Carbon\Carbon::parse($primerMovimiento->au_fechacr)->format('d/m/Y h:i A') : '' }}
                                        </span>
                                        @php
                                            $areaInicial = optional($primerMovimiento->areaDestino)->nombre_area ?? optional($primerMovimiento->areaOrigen)->nombre_area;
                                        @endphp
                                        @if(!empty($areaInicial))
                                        <span>
                                            <i class="bi bi-building me-1 fs-8"></i>
                                            Área: <span class="fw-semibold text-gray-800">{{ $areaInicial }}</span>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>

        @elseif(isset($resultado) && !$resultado)
        <div class="card border-dashed border-warning bg-light-warning">
            <div class="card-body text-center py-15">
                <i class="bi bi-search fs-3x text-warning mb-4"></i>
                <h2 class="fs-2 fw-bold text-gray-800 mb-2">Expediente no encontrado</h2>
                <p class="text-gray-600 fs-5">
                    El número <strong>"{{ request('expediente') }}"</strong> no existe en nuestros registros.<br>
                    Por favor verifica el código e intenta nuevamente.
                </p>
            </div>
        </div>
        @endif

        @if(isset($resultado) && $resultado && $estadoNombre === 'ARCHIVADO')
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
                                <div>Tu solicitud será revisada por Mesa de Partes. Describe claramente el motivo de la rectificación.</div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="fw-bold text-gray-600 mb-1">Expediente</div>
                                    <div class="text-gray-800 fs-5">{{ $resultado->expediente_documento }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-bold text-gray-600 mb-1">N° Documento</div>
                                    <div class="text-gray-800 fs-5">{{ $resultado->numero_documento }}</div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="fw-bold text-gray-600 mb-1">Asunto</div>
                                <div class="text-gray-800">{{ $resultado->asunto_documento }}</div>
                            </div>

                            <div class="separator my-4"></div>

                            <div class="mb-3">
                                <label class="form-label fw-bold required">Motivo de la rectificación</label>
                                <textarea name="motivo" class="form-control @error('motivo') is-invalid @enderror" rows="4" maxlength="500" placeholder="Describe el motivo de tu solicitud..." required>{{ old('motivo') }}</textarea>
                                <div class="form-text">Máximo 500 caracteres.</div>
                                @error('motivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Archivos de evidencia (opcional)</label>
                                <input type="file" name="archivos_evidencia[]" id="archivos-evidencia-input" class="form-control @error('archivos_evidencia') is-invalid @enderror @error('archivos_evidencia.*') is-invalid @enderror" multiple accept=".pdf,.png,.jpg,.jpeg" style="display: none;">
                                <button type="button" class="btn btn-light-primary w-100" onclick="document.getElementById('archivos-evidencia-input').click()">
                                    <i class="bi bi-upload me-2"></i> Elegir archivos
                                </button>
                                <div class="form-text mt-2">Puedes adjuntar archivos PDF o imágenes (máx. 10MB cada uno)</div>
                                @error('archivos_evidencia')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('archivos_evidencia.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <div id="archivos-preview-container" class="mt-3" style="display: none;">
                                    <div class="fw-semibold text-gray-700 mb-2">
                                        <i class="bi bi-paperclip me-1"></i> Archivos a subir (<span id="archivos-count">0</span>)
                                    </div>
                                    <div id="archivos-preview" class="row g-3"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i> Enviar solicitud
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <div class="modal fade" id="modal-detalle-movimiento" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Detalle del Movimiento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="fw-bold text-gray-600 mb-2 d-block">Estado</label>
                            <span class="badge bg-primary fs-6" id="modal-estado"></span>
                        </div>
                        <div class="mb-4">
                            <label class="fw-bold text-gray-600 mb-2 d-block">Fecha</label>
                            <span class="text-gray-800 fs-6" id="modal-fecha"></span>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold text-gray-600 mb-2 d-block">Motivo / Observación</label>
                            <div class="p-3 bg-light rounded text-break" id="modal-motivo" style="min-height: 100px; white-space: pre-wrap;"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="footer">
        <p class="mb-0">© {{ date('Y') }} Universidad Nacional Intercultural de la Amazonía - SGD v1.0</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Manejo de vista previa de archivos
        let selectedFiles = [];
        const inputFile = document.getElementById('archivos-evidencia-input');
        const previewContainer = document.getElementById('archivos-preview-container');
        const previewDiv = document.getElementById('archivos-preview');
        const countSpan = document.getElementById('archivos-count');
        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB por archivo
        const MAX_TOTAL_SIZE = 40 * 1024 * 1024; // 40MB total

        if (inputFile) {
            inputFile.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);

                // Validar tamaño de cada archivo
                for (const file of files) {
                    if (file.size > MAX_FILE_SIZE) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Archivo demasiado grande',
                            text: `El archivo "${file.name}" excede el límite de 10MB. Por favor, selecciona un archivo más pequeño.`,
                            confirmButtonColor: '#f1416c'
                        });
                        return;
                    }
                }

                // Agregar archivos a la lista
                selectedFiles = [...selectedFiles, ...files];

                // Validar tamaño total
                const totalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);
                if (totalSize > MAX_TOTAL_SIZE) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tamaño total excedido',
                        text: `El tamaño total de los archivos (${(totalSize / 1024 / 1024).toFixed(2)}MB) excede el límite de 40MB. Por favor, quita algunos archivos.`,
                        confirmButtonColor: '#f1416c'
                    });
                    // Remover los últimos archivos agregados
                    selectedFiles = selectedFiles.slice(0, selectedFiles.length - files.length);
                }

                updateFilePreview();
            });
        }

        function updateFilePreview() {
            if (selectedFiles.length === 0) {
                previewContainer.style.display = 'none';
                return;
            }

            previewContainer.style.display = 'block';
            countSpan.textContent = selectedFiles.length;
            previewDiv.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const fileSize = (file.size / 1024).toFixed(0) + ' KB';
                const fileName = file.name.length > 25 ? file.name.substring(0, 22) + '...' : file.name;
                const isImage = file.type.startsWith('image/');
                const isPDF = file.type === 'application/pdf';

                const iconClass = isPDF ? 'bi-file-earmark-pdf text-danger' : 'bi-image text-primary';

                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';
                col.innerHTML = `
                    <div class="card border border-gray-300 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3">
                                    <i class="bi ${iconClass} fs-2x"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-gray-800 text-truncate" title="${file.name}">${fileName}</div>
                                    <div class="text-muted fs-7">${fileSize}</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-light-danger w-100" onclick="removeFile(${index})">
                                <i class="bi bi-trash me-1"></i> Quitar
                            </button>
                        </div>
                    </div>
                `;
                previewDiv.appendChild(col);
            });

            // Actualizar el input con los archivos seleccionados
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            inputFile.files = dataTransfer.files;
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateFilePreview();
        }

        // Llenar modal de detalle del movimiento
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

    @if(session('status'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Solicitud enviada!',
            text: '{{ session('status') }}',
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
            text: '{{ $errors->first('motivo') }}',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#f1416c'
        });
    </script>
    @endif

</body>
</html>
