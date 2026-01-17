<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Estadísticas de Documentos">
        <x-breadcrumb.item titulo="Reportes" enlace="{{ route('reportes.index') }}" />
        <x-breadcrumb.item titulo="Estadísticas" />
    </x-breadcrumb>
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="ki-outline ki-chart-line fs-2 me-2"></i>
                        Estadísticas de Documentos
                    </h3>

                    <div class="card-toolbar d-flex gap-2">
                        <a href="{{ route('reportes.index') }}" class="btn btn-sm btn-light">
                            <i class="ki-outline ki-arrow-left fs-4 me-1"></i>
                            Regresar
                        </a>

                        <button class="btn btn-sm btn-primary" onclick="window.print()">
                            <i class="ki-outline ki-printer fs-4 me-1"></i>
                            Exportar
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="d-flex gap-3 mb-7 overflow-auto pb-2">
                        @foreach(['hoy' => 'Hoy', 'semana' => 'Esta Semana', 'mes' => 'Este Mes', 'anio' => 'Este Año'] as $key => $label)
                        <button wire:click="cambiarFiltro('{{ $key }}')"
                                class="btn btn-sm {{ $filtroTiempo === $key ? 'btn-primary' : 'btn-light-primary' }} text-nowrap">
                            {{ $label }}
                        </button>
                        @endforeach
                        <div wire:loading wire:target="cambiarFiltro" class="ms-2 align-self-center text-primary">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                        </div>
                    </div>

                    <div class="row g-4 mb-7">
                        <div class="col-md-4">
                            <div class="card bg-light-primary border-0 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-6">
                                    <div>
                                        <span class="text-muted fw-semibold d-block fs-7 mb-2">Total Documentos</span>
                                        <span class="text-gray-800 fw-bold fs-2x d-block mb-1">{{ $totalDocumentos }}</span>
                                        <span class="text-{{ $porcentajeCambioMesAnterior >= 0 ? 'success' : 'danger' }} fs-7">
                                            <i class="ki-outline ki-arrow-{{ $porcentajeCambioMesAnterior >= 0 ? 'up' : 'down' }} fs-7"></i>
                                            {{ abs($porcentajeCambioMesAnterior) }}% vs mes anterior
                                        </span>
                                    </div>
                                    <i class="ki-outline ki-document text-primary fs-3x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-warning border-0 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-6">
                                    <div>
                                        <span class="text-muted fw-semibold d-block fs-7 mb-2">Pendientes</span>
                                        <span class="text-gray-800 fw-bold fs-2x d-block mb-1">{{ $documentosPendientes }}</span>
                                        <span class="text-muted fs-7">Acción requerida</span>
                                    </div>
                                    <i class="ki-outline ki-time text-warning fs-3x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-success border-0 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-6">
                                    <div>
                                        <span class="text-muted fw-semibold d-block fs-7 mb-2">Recepcionados</span>
                                        <span class="text-gray-800 fw-bold fs-2x d-block mb-1">{{ $documentosRecepcionados }}</span>
                                        <span class="text-muted fs-7">Procesados correctamente</span>
                                    </div>
                                    <i class="ki-outline ki-check-circle text-success fs-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-5 mb-7">
                        <div class="col-md-6">
                            <div class="card border border-gray-300 h-100 shadow-sm">
                                <div class="card-header min-h-auto py-3">
                                    <h4 class="card-title fs-5 mb-0">Distribución por Estado</h4>
                                </div>
                                <div class="card-body d-flex justify-content-center align-items-center" style="height: 320px;">
                                    <canvas id="chartEstados"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border border-gray-300 h-100 shadow-sm">
                                <div class="card-header min-h-auto py-3">
                                    <h4 class="card-title fs-5 mb-0">Tendencia Mensual</h4>
                                </div>
                                <div class="card-body" style="height: 320px;">
                                    <canvas id="chartTendencia"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-5">
                        <div class="col-md-6">
                            <div class="card border border-gray-300 h-100 shadow-sm">
                                <div class="card-header min-h-auto py-3">
                                    <h4 class="card-title fs-5 mb-0">Tiempo Promedio de Respuesta (Días)</h4>
                                </div>
                                <div class="card-body overflow-auto" style="max-height: 320px;">
                                    @if(!empty($datosTiempoRespuesta['areas']))
                                        @foreach($datosTiempoRespuesta['areas'] as $index => $area)
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-gray-700 fw-semibold fs-7">{{ $area }}</span>
                                                    <span class="badge badge-light fw-bold">{{ round($datosTiempoRespuesta['valores'][$index]['dias']) }} días</span>
                                                </div>
                                                <div class="progress h-6px rounded-pill">
                                                    <div class="progress-bar bg-{{ ['primary', 'info', 'success', 'warning'][$index % 4] }}"
                                                         style="width: {{ $datosTiempoRespuesta['valores'][$index]['porcentaje'] }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                            <i class="ki-outline ki-information fs-2x mb-2"></i>
                                            <p>No hay datos de tiempos registrados</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border border-gray-300 h-100 shadow-sm">
                                <div class="card-header min-h-auto py-3">
                                    <h4 class="card-title fs-5 mb-0">Documentos por Tipo (Top 5)</h4>
                                </div>
                                <div class="card-body" style="height: 320px;">
                                    <canvas id="chartTipos"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- USO DE ASSETS PARA CARGAR CHART.JS --}}
@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
@endassets

{{-- SCRIPT EN LIVEWIRE 3 --}}
@script
<script>
    let chartEstadosInstance = null;
    let chartTendenciaInstance = null;
    let chartTiposInstance = null;

    // Función para crear/actualizar gráficos
    const renderCharts = (data) => {

        // 1. Gráfico Estados (Dona)
        const ctxEstados = document.getElementById('chartEstados');
        if (ctxEstados) {
            if (chartEstadosInstance) chartEstadosInstance.destroy(); // Destruir anterior

            // Si no hay datos, mostrar algo por defecto
            let labelsEstados = data.estados.labels.length > 0 ? data.estados.labels : ['Sin datos'];
            let dataEstados = data.estados.valores.length > 0 ? data.estados.valores : [1];
            let colorsEstados = data.estados.valores.length > 0
                ? ['#7239EA', '#50CD89', '#FFC700', '#F1416C', '#009EF7']
                : ['#E4E6EF'];

            chartEstadosInstance = new Chart(ctxEstados, {
                type: 'doughnut',
                data: {
                    labels: labelsEstados,
                    datasets: [{
                        data: dataEstados,
                        backgroundColor: colorsEstados,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8 } }
                    },
                    cutout: '75%'
                }
            });
        }

        // 2. Gráfico Tendencia (Línea)
        const ctxTendencia = document.getElementById('chartTendencia');
        if (ctxTendencia) {
            if (chartTendenciaInstance) chartTendenciaInstance.destroy();

            chartTendenciaInstance = new Chart(ctxTendencia, {
                type: 'line',
                data: {
                    labels: data.tendencia.labels,
                    datasets: [{
                        label: 'Documentos',
                        data: data.tendencia.valores,
                        borderColor: '#009EF7',
                        backgroundColor: 'rgba(0, 158, 247, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 3. Gráfico Tipos (Barras)
        const ctxTipos = document.getElementById('chartTipos');
        if (ctxTipos) {
            if (chartTiposInstance) chartTiposInstance.destroy();

            chartTiposInstance = new Chart(ctxTipos, {
                type: 'bar',
                data: {
                    labels: data.tipos.labels,
                    datasets: [{
                        label: 'Cantidad',
                        data: data.tipos.valores,
                        backgroundColor: ['#009EF7', '#50CD89', '#F1416C', '#FFC700', '#7239EA'],
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    }

    // Inicializar al cargar
    renderCharts({
        estados: @json($datosEstados),
        tendencia: @json($datosTendencia),
        tipos: @json($datosTiposDocumento)
    });
    // Escuchar evento de actualización (cuando cambian los filtros)
    Livewire.on('actualizar-graficos', (eventData) => {
        renderCharts(eventData[0]);
    });
</script>
@endscript
</div>
