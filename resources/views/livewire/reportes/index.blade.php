<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Reportes">
        <x-breadcrumb.item titulo="Reportes" />
    </x-breadcrumb>
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-chart-line fs-2 me-2"></i>
                        Módulo de Reportes
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center py-10">
                        <i class="ki-outline ki-file-down fs-5x text-primary mb-5"></i>
                        <h3 class="text-gray-800 mb-3">Generación de Reportes</h3>
                        <p class="text-muted fs-5 mb-5">
                            Aquí podrás generar diferentes tipos de reportes del sistema de gestión documental.
                        </p>

                        <div class="row g-5 mt-5">
                            <div class="col-md-4">
                                <div class="card border border-dashed border-gray-300 h-100">
                                    <div class="card-body d-flex flex-column justify-content-center">
                                        <i class="ki-outline ki-document fs-3x text-primary mb-3"></i>
                                        <h4 class="mb-2">Reporte de Documentos</h4>
                                        <p class="text-muted fs-6 mb-3">Genera reportes de documentos registrados, filtrados por fecha, estado y área.</p>
                                        <a href="{{ route('reportes.documentos') }}" class="btn btn-sm btn-primary">
                                            Ver reporte
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border border-dashed border-gray-300 h-100">
                                    <div class="card-body d-flex flex-column justify-content-center">
                                        <i class="ki-outline ki-chart-simple fs-3x text-success mb-3"></i>
                                        <h4 class="mb-2">Estadísticas</h4>
                                        <p class="text-muted fs-6 mb-3">Visualiza estadísticas de documentos procesados, tiempos de respuesta y más.</p>
                                        <a href="{{ route('reportes.estadisticas') }}" class="btn btn-sm btn-success">
                                            Ver estadísticas
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border border-dashed border-gray-300 h-100">
                                    <div class="card-body d-flex flex-column justify-content-center">
                                        <i class="ki-outline ki-user-tick fs-3x text-info mb-3"></i>
                                        <h4 class="mb-2">Reporte de Usuarios</h4>
                                        <p class="text-muted fs-6 mb-3">Visualiza todas las acciones y movimientos de usuarios en el sistema.</p>
                                        <a href="{{ route('reportes.usuarios') }}" class="btn btn-sm btn-info">
                                            Ver reporte
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
