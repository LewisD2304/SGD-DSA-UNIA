<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta tu Trámite - SGD</title>
    <style>
        body{font-family: system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:#f5f7fb;margin:0}
        .header{background:#b30000;color:#fff;padding:16px 24px;font-weight:600;letter-spacing:.5px}
        .container{max-width:960px;margin:24px auto;background:#fff;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.06);overflow:hidden}
        .hero{display:flex;align-items:center;gap:16px;background:#f9f9fb;padding:24px}
        .hero h1{margin:0;font-size:22px}
        .content{padding:24px}
        .form-row{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
        input[type=text]{flex:1;padding:10px 12px;border:1px solid #d3d7df;border-radius:6px;font-size:15px}
        button{background:#0f62fe;color:#fff;border:none;border-radius:6px;padding:10px 16px;font-size:15px;cursor:pointer}
        button:hover{background:#0353e9}
        .help{color:#c00;margin-left:4px}
        .card{border:1px solid #e6e8ee;border-radius:8px;margin-top:16px}
        .card h2{margin:0;padding:12px 16px;background:#f6f7fb;border-bottom:1px solid #e6e8ee;font-size:16px}
        .card .body{padding:12px 16px}
        .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
        .item{padding:8px;border:1px dashed #e6e8ee;border-radius:6px;background:#fff}
        .label{color:#555;font-size:12px}
        .value{font-weight:600}
        .muted{color:#777}
        .empty{padding:12px 16px;color:#777}
        .footer{padding:16px;color:#666;text-align:center}
    </style>
</head>
<body>
<div class="header">CONSULTA TU TRÁMITE</div>
<div class="container">
    <div class="hero">
        <img src="/assets/media/logo.png" alt="SGD" style="height:48px" onerror="this.style.display='none'">
        <h1>Sistema de Gestión Documentaria</h1>
    </div>
    <div class="content">
        <form method="post" action="{{ route('consulta.buscar') }}">
            @csrf
            <div class="form-row">
                <label for="expediente">Número de Expediente:</label>
                <input type="text" id="expediente" name="expediente" value="{{ old('expediente', $expediente ?? '') }}" placeholder="EJEMPLO: EXP-001-2025" required>
                <span class="help">(*)</span>
                <button type="submit">Buscar Expediente</button>
            </div>
            @error('expediente')
            <div class="muted" style="margin-top:6px;color:#c00">{{ $message }}</div>
            @enderror
        </form>

        @if(isset($resultado))
            @if($resultado)
                <div class="card" style="margin-top:16px">
                    <h2>Estado Actual del Trámite</h2>
                    <div class="body">
                        <div class="grid">
                            <div class="item">
                                <div class="label">Expediente</div>
                                <div class="value">{{ $resultado->expediente_documento }}</div>
                            </div>
                            <div class="item">
                                <div class="label">Número de Documento</div>
                                <div class="value">{{ $resultado->numero_documento ?? '—' }}</div>
                            </div>
                            <div class="item">
                                <div class="label">Asunto</div>
                                <div class="value">{{ $resultado->asunto_documento ?? '—' }}</div>
                            </div>
                            <div class="item">
                                <div class="label">Estado</div>
                                <div class="value">{{ optional($resultado->estado)->nombre_estado ?? '—' }}</div>
                            </div>
                            <div class="item">
                                <div class="label">Área Remitente</div>
                                <div class="value">{{ optional($resultado->areaRemitente)->nombre_area ?? '—' }}</div>
                            </div>
                            <div class="item">
                                <div class="label">Área Actual (Destino)</div>
                                <div class="value">{{ optional($resultado->areaDestino)->nombre_area ?? '—' }}</div>
                            </div>
                            <div class="item">
                                <div class="label">Fecha de Emisión</div>
                                <div class="value">{{ $resultado->fecha_emision_documento ? \Carbon\Carbon::parse($resultado->fecha_emision_documento)->format('d/m/Y H:i') : '—' }}</div>
                            </div>
                            <div class="item">
                                <div class="label">Fecha de Recepción</div>
                                <div class="value">{{ $resultado->fecha_recepcion_documento ? \Carbon\Carbon::parse($resultado->fecha_recepcion_documento)->format('d/m/Y H:i') : '—' }}</div>
                            </div>
                        </div>

                        <div class="muted" style="margin-top:12px">Historial reciente</div>
                        <ul class="muted">
                            @forelse($resultado->movimientos()->orderByDesc('au_fechacr')->limit(5)->get() as $mov)
                                <li>
                                    {{ optional($mov->estado)->nombre_estado ?? 'Movimiento' }}
                                    — {{ $mov->au_fechacr ? \Carbon\Carbon::parse($mov->au_fechacr)->format('d/m/Y H:i') : '' }}
                                </li>
                            @empty
                                <li>No hay movimientos registrados.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @else
                <div class="card" style="margin-top:16px">
                    <h2>Resultado de la Búsqueda</h2>
                    <div class="empty">No se encontró ningún expediente con el número ingresado.</div>
                </div>
            @endif
        @endif
    </div>
    <div class="footer">Versión 1.0</div>
</div>
</body>
</html>
