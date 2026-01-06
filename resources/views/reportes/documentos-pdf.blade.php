<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Documentos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4472C4;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #4472C4;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .info-section {
            margin-bottom: 15px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }
        .info-section strong {
            color: #4472C4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #4472C4;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE DOCUMENTOS</h1>
        <p>Sistema de Gestión Documental - Universidad Nacional Intercultural de la Amazonía</p>
    </div>

    <div class="info-section">
        <strong>Período:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }} &nbsp;&nbsp;
        <strong>Tipo:</strong> {{ ucfirst($tipoReporte) }} &nbsp;&nbsp;
        <strong>Total Registros:</strong> {{ $total }} &nbsp;&nbsp;
        <strong>Generado:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">N° Documento</th>
                <th style="width: 8%;">Expediente</th>
                <th style="width: 25%;">Asunto</th>
                <th style="width: 12%;">Remitente</th>
                <th style="width: 12%;">Origen</th>
                <th style="width: 12%;">Destino</th>
                <th style="width: 10%;">Estado</th>
                <th style="width: 5%;">Folios</th>
                <th style="width: 10%;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documentos as $doc)
            <tr>
                <td><strong>{{ $doc->numero_documento }}</strong></td>
                <td>{{ $doc->expediente_documento ?? 'S/N' }}</td>
                <td class="text-truncate">{{ Str::limit($doc->asunto_documento ?? $doc->asunto, 80) }}</td>
                <td>{{ Str::limit($doc->remitente ?? 'N/A', 30) }}</td>
                <td>{{ Str::limit($doc->areaRemitente->nombre_area ?? 'Externo', 25) }}</td>
                <td>{{ Str::limit($doc->areaDestino->nombre_area ?? 'Sin asignar', 25) }}</td>
                <td>
                    @php
                        $estado = strtoupper($doc->estado->nombre_estado ?? 'N/A');
                        $badgeClass = match(true) {
                            str_contains($estado, 'RECEPCIONADO') || str_contains($estado, 'FINALIZADO') => 'badge-success',
                            str_contains($estado, 'PENDIENTE') => 'badge-warning',
                            str_contains($estado, 'OBSERVADO') => 'badge-danger',
                            str_contains($estado, 'ARCHIVADO') => 'badge-secondary',
                            default => 'badge-secondary'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $estado }}</span>
                </td>
                <td style="text-align: center;">{{ $doc->folio_documento ?? 0 }}</td>
                <td>{{ \Carbon\Carbon::parse($doc->au_fechacr)->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                    No se encontraron documentos con los filtros aplicados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión Documental</p>
        <p>{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
