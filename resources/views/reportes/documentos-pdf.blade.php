<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Documentos - UNIA</title>
    <style>
        /* RESET Y BASE */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif; /* Fuente segura para PDF */
            font-size: 10px;
            color: #333;
            padding: 30px;
        }

        /* COLORES Y VARIABLES VISUALES */
        .color-primary { color: #0056b3; } /* Azul institucional similar a la imagen */
        .border-primary { border: 2px solid #0056b3; }

        /* CABECERA (Usamos tablas para alineación perfecta en PDF) */
        .header-layout { width: 100%; margin-bottom: 10px; }
        .header-layout td { vertical-align: top; }

        /* LOGO Y TEXTOS IZQUIERDA */
        .logo-container { width: 70px; padding-right: 15px; }
        .logo { width: 60px; height: auto; }

        .university-info h1 {
            font-size: 14px;
            font-weight: 800;
            color: #0056b3;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .university-info h2 {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .university-info p {
            font-size: 9px;
            color: #555;
            line-height: 1.2;
        }

        /* CUADRO TIPO BOTÓN SUPERIOR */
        .top-title-box {
            display: inline-block;
            border: 2px solid #0056b3;
            color: #0056b3;
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        /* CUADRO RESUMEN DERECHA (Con bordes redondeados) */
        .summary-box-container {
            text-align: right;
            width: 280px; /* Ancho fijo para el cuadro derecho */
        }
        .summary-box {
            background-color: #f8f9fa; /* Fondo muy suave */
            border: 2px solid #0056b3; /* Borde Azul */
            border-radius: 12px; /* BORDES REDONDEADOS SOLICITADOS */
            padding: 10px 12px;
            text-align: left;
        }
        .summary-item {
            font-size: 9px;
            margin-bottom: 3px;
            color: #333;
        }
        .summary-item strong {
            color: #0056b3;
            font-weight: bold;
            margin-right: 4px;
        }

        /* SEPARADOR AZUL */
        .blue-divider {
            width: 100%;
            height: 2px;
            background-color: #0056b3;
            margin: 15px 0 20px 0;
        }

        /* TABLA DE DATOS */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #f2f2f2; /* Fondo gris claro encabezado */
            color: #0056b3; /* Texto azul encabezado */
            font-weight: bold;
            font-size: 9px;
            padding: 10px 6px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            border-top: 1px solid #ddd;
        }
        .data-table td {
            padding: 8px 6px;
            font-size: 9px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
            color: #333;
        }

        /* ESTILOS ESPECÍFICOS DE COLUMNA */
        .col-bold { font-weight: bold; color: #000; }
        .col-gray { color: #666; font-size: 8.5px; }

        /* BADGES (ESTADOS) */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #ddd;
        }
        /* Colores Badge similares a la imagen */
        .bg-rectificar { background-color: #e2e6ea; color: #383d41; border-color: #d6d8db; } /* Gris */
        .bg-archivado { background-color: #e2e6ea; color: #383d41; border-color: #d6d8db; } /* Gris */
        .bg-recepcionado { background-color: #d1e7dd; color: #0f5132; border-color: #badbcc; } /* Verde suave */
        .bg-pendiente { background-color: #fff3cd; color: #856404; border-color: #ffecb5; } /* Amarillo */
        .bg-observado { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; } /* Rojo */

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="top-title-box">
        REPORTE DE DOCUMENTOS
    </div>

    <table class="header-layout">
        <tr>
            <td class="logo-container">
                <img src="{{ public_path('assets/media/logo-unia.webp') }}" class="logo" alt="UNIA">
            </td>

            <td class="university-info">
                <h1>UNIVERSIDAD NACIONAL INTERCULTURAL DE LA AMAZONIA</h1>
                <h2>DIRECCIÓN DE SERVICIOS ACADÉMICOS</h2>
                <p>Sistema de Gestión Documental</p>
                <p>Car. San José km. 0.9 Cas. San José (Costado Instituto Bilingüe) - RUC 20393146557</p>
            </td>

            <td class="summary-box-container">
                <div class="summary-box">
                    <div class="summary-item">
                        <strong>Período:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </div>
                    <div class="summary-item">
                        <strong>Tipo:</strong> {{ ucfirst($tipoReporte) === 'Todos' ? 'Todos' : ucfirst($tipoReporte) }}
                    </div>
                    <div class="summary-item">
                        <strong>Total Registros:</strong> {{ $total }}
                    </div>
                    <div class="summary-item">
                        <strong>Generado:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="blue-divider"></div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="12%">N° Documento</th>
                <th width="10%">Expediente</th>
                <th width="22%">Asunto</th>
                <th width="10%">Remitente</th>
                <th width="10%">Origen</th>
                <th width="10%">Destino</th>
                <th width="12%" style="text-align: center;">Estado</th>
                <th width="5%" style="text-align: center;">Folios</th>
                <th width="9%" style="text-align: center;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documentos as $doc)
            <tr>
                <td class="col-bold">
                    {{ $doc->numero_documento }}
                </td>

                <td class="col-gray">
                    {{ $doc->expediente_documento ?? '-' }}
                </td>

                <td>
                    {{ Str::limit($doc->asunto_documento ?? $doc->asunto, 85) }}
                </td>

                <td class="col-gray">
                    {{ Str::limit($doc->remitente ?? 'N/A', 25) }}
                </td>

                <td>
                    {{ Str::limit($doc->areaRemitente->nombre_area ?? 'EXTERNO', 20) }}
                </td>

                <td>
                    {{ Str::limit($doc->areaDestino->nombre_area ?? 'SIN ASIGNAR', 20) }}
                </td>

                <td style="text-align: center;">
                    @php
                        $estado = strtoupper($doc->estado->nombre_estado ?? 'N/A');
                        $class = 'bg-archivado'; // Por defecto gris

                        if(str_contains($estado, 'RECEPCIONADO') || str_contains($estado, 'FINALIZADO')) {
                            $class = 'bg-recepcionado';
                        } elseif(str_contains($estado, 'PENDIENTE')) {
                            $class = 'bg-pendiente';
                        } elseif(str_contains($estado, 'OBSERVADO') || str_contains($estado, 'ANULADO')) {
                            $class = 'bg-observado';
                        } elseif(str_contains($estado, 'RECTIFICAR')) {
                            $class = 'bg-rectificar'; // Gris según imagen
                        }
                    @endphp
                    <span class="badge {{ $class }}">{{ $estado }}</span>
                </td>

                <td style="text-align: center;">
                    {{ $doc->folio_documento ?? 0 }}
                </td>

                <td style="text-align: center;">
                    {{ \Carbon\Carbon::parse($doc->au_fechacr)->format('d/m/Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #777;">
                    No se encontraron documentos para este reporte.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Universidad Nacional Intercultural de la Amazonía - Sistema de Gestión Documental | {{ date('Y') }}
    </div>

</body>
</html>
