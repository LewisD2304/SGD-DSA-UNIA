<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Reporte de Usuarios">
        <x-breadcrumb.item titulo="Reportes" />
        <x-breadcrumb.item titulo="/" />
        <x-breadcrumb.item titulo="Usuarios" />
    </x-breadcrumb>
    @endsection

    <div class="app-container container-fluid">

        <div class="row g-5 g-xl-8 mb-8">
            <div class="col-md-3">
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end" style="background-color: #f3f6ff; background-image: url('assets/media/svg/shapes/wave-blue.svg')">
                    <div class="card-body pb-0">
                        <span class="fw-bold fs-8 text-gray-400 d-block mb-7">Usuarios Activos</span>
                        <div class="mb-7"><span class="fw-bold fs-3qx text-gray-800">{{ $totalUsuariosActivos }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end" style="background-color: #fff5ed; background-image: url('assets/media/svg/shapes/wave-orange.svg')">
                    <div class="card-body pb-0">
                        <span class="fw-bold fs-8 text-gray-400 d-block mb-7">Total Acciones</span>
                        <div class="mb-7"><span class="fw-bold fs-3qx text-gray-800">{{ collect($accionesPorTipo)->sum('cantidad') }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end" style="background-color: #f1fdf9; background-image: url('assets/media/svg/shapes/wave-green.svg')">
                    <div class="card-body pb-0">
                        <span class="fw-bold fs-8 text-gray-400 d-block mb-7">Documentos Creados</span>
                        <div class="mb-7"><span class="fw-bold fs-3qx text-gray-800">{{ collect($accionesPorTipo)->firstWhere('tipo', 'Documentos Creados')['cantidad'] ?? 0 }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end" style="background-color: #fdf3ff; background-image: url('assets/media/svg/shapes/wave-purple.svg')">
                    <div class="card-body pb-0">
                        <span class="fw-bold fs-8 text-gray-400 d-block mb-7">Movimientos</span>
                        <div class="mb-7"><span class="fw-bold fs-3qx text-gray-800">{{ collect($accionesPorTipo)->firstWhere('tipo', 'Movimientos Creados')['cantidad'] ?? 0 }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5 g-xl-8 mb-8">
            <div class="col-md-6 col-xl-6">
                <div class="card card-flush h-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column"><span class="card-label fw-bold text-dark">Usuarios Más Activos</span></h3>
                    </div>
                    <div class="card-body pt-5">
                        @forelse ($usuariosMasActivos as $usuario)
                            <div class="d-flex align-items-center mb-7">
                                <div class="symbol symbol-40px me-5">
                                    <div class="symbol-label bg-light-primary"><span class="fs-6 fw-bold text-primary">{{ strtoupper(substr($usuario->nombres_persona ?? $usuario->nombre_usuario, 0, 1)) }}</span></div>
                                </div>
                                <div class="flex-grow-1">
                                    @php $nombreCompleto = $usuario->nombres_persona ? $usuario->nombres_persona . ' ' . $usuario->apellido_paterno_persona : 'SISTEMA'; @endphp
                                    <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ strtoupper($nombreCompleto) }}</a>
                                    <span class="text-muted d-block fw-semibold fs-7">{{ $usuario->nombre_usuario }}</span>
                                </div>
                                <div class="d-flex flex-column align-items-end"><span class="badge badge-light fs-8 fw-bold">{{ $usuario->total_acciones }} acciones</span></div>
                            </div>
                        @empty
                            <div class="text-center py-4"><p class="text-muted fs-7">No hay actividad registrada</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-6">
                <div class="card card-flush h-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column"><span class="card-label fw-bold text-dark">Acciones por Tipo</span></h3>
                    </div>
                    <div class="card-body pt-5">
                        @foreach ($accionesPorTipo as $accion)
                            <div class="d-flex align-items-center mb-7">
                                <div class="symbol symbol-40px me-5">
                                    <div class="symbol-label" style="background-color: {{ $accion['color'] }}20"><i class="ki-outline {{ str_contains($accion['tipo'], 'Documento') ? 'ki-file' : 'ki-arrow-two-diagonals' }} fs-2" style="color: {{ $accion['color'] }}"></i></div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="text-gray-800 fw-bold fs-6 d-block">{{ $accion['tipo'] }}</span>
                                    <span class="text-muted fw-semibold fs-7">Registros totales</span>
                                </div>
                                <span class="badge badge-light fw-bold fs-8">{{ $accion['cantidad'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title fw-bold">
                    <i class="ki-duotone ki-clock fs-1 text-primary me-2"><span class="path1"></span><span class="path2"></span></i>
                    Registro de Actividades
                </h3>
                <div class="card-toolbar gap-2">
                    <button type="button" class="btn btn-light btn-active-light-danger" wire:click="limpiarFiltros">
                        <i class="ki-outline ki-filter-x fs-2 px-0"></i> Limpiar Filtros
                    </button>
                </div>
            </div>

            <div class="card-body border-top py-6">
                <div class="row g-3 mb-6">
                    <div class="col-md-2"><label class="form-label fw-semibold">Desde</label><input type="date" class="form-control" wire:model.live="fechaInicio"></div>
                    <div class="col-md-2"><label class="form-label fw-semibold">Hasta</label><input type="date" class="form-control" wire:model.live="fechaFin"></div>
                    <div class="col-md-2"><label class="form-label fw-semibold">Usuario</label><select class="form-select" wire:model.live="usuarioFiltro"><option value="">Todos</option>@foreach ($usuarios as $usuario)<option value="{{ $usuario->id_usuario }}">{{ $usuario->nombre_usuario }}</option>@endforeach</select></div>
                    <div class="col-md-2"><label class="form-label fw-semibold">Tipo Acción</label><select class="form-select" wire:model.live="tipoAccionFiltro"><option value="">Todos</option><option value="Crear Documento">Crear Documento</option><option value="Editar Documento">Editar Documento</option><option value="Crear Movimiento">Crear Movimiento</option><option value="Editar Movimiento">Editar Movimiento</option></select></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Búsqueda</label><input type="text" class="form-control" wire:model.live.debounce.500ms="buscar" placeholder="Buscar en descripción..."></div>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                <th class="min-w-150px">Usuario</th>
                                <th class="min-w-100px">Tipo Acción</th>
                                <th class="min-w-200px">Descripción</th>
                                <th class="min-w-150px">Fecha y Hora</th>
                                <th class="min-w-100px">Tabla</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600" wire:loading.class="opacity-50" wire:target="buscar, updated, fechaInicio, fechaFin, usuarioFiltro, tipoAccionFiltro">
                            @forelse ($actividades as $actividad)
                                <tr>
                                    <td>
                                        @php
                                            $usuario = collect($usuarios)->firstWhere('id_usuario', $actividad->usuario_id);
                                            $nombreCompleto = 'Sistema';
                                            if ($usuario && $usuario->nombres_persona) {
                                                $nombreCompleto = $usuario->nombres_persona . ' ' . $usuario->apellido_paterno_persona . ' ' . $usuario->apellido_materno_persona;
                                            } elseif ($usuario) {
                                                $nombreCompleto = $usuario->nombre_usuario;
                                            }
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px me-2">
                                                <div class="symbol-label bg-light-primary fs-7 fw-bold text-primary">{{ strtoupper(substr($nombreCompleto, 0, 1)) }}</div>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold mb-1 fs-6">{{ strtoupper($nombreCompleto) }}</span>
                                                <span class="text-muted fs-7 fw-semibold">{{ $usuario->nombre_usuario ?? 'Sistema' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-{{ str_contains($actividad->tipo_accion, 'Crear') ? 'success' : 'warning' }} fw-bold">{{ $actividad->tipo_accion }}</span>
                                    </td>
                                    <td style="max-width: 350px;">
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-semibold fs-7 text-truncate" title="{{ $actividad->descripcion }}">{{ $actividad->descripcion }}</span>
                                            @if(str_contains($actividad->descripcion, ' - '))
                                            <span class="text-muted fs-8">{{ explode(' - ', $actividad->descripcion)[0] }} </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-gray-600 fw-bold fs-7">
                                            {{ \Carbon\Carbon::parse($actividad->fecha_accion)->locale('es')->isoFormat('D MMM. YYYY, h:mm a') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-{{ $actividad->tipo_tabla === 'documento' ? 'primary' : 'info' }} fw-bold">{{ ucfirst($actividad->tipo_tabla) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-muted">No hay registros en el sistema para los filtros seleccionados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div wire:loading.flex wire:target="buscar, updated, fechaInicio, fechaFin, usuarioFiltro, tipoAccionFiltro" class="position-absolute top-50 start-50 translate-middle justify-content-center">
                        <div class="d-flex flex-column align-items-center">
                            <x-spinner class="text-primary mb-2" style="width: 35px; height: 35px;" />
                            <span class="text-muted fs-7">Cargando datos...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end py-6">
                {{ $actividades->links() }}
                {{-- Si usas Bootstrap 4: {{ $actividades->links('pagination::bootstrap-4') }} --}}
                {{-- Si usas Bootstrap 5: {{ $actividades->links('pagination::bootstrap-5') }} --}}
            </div>
        </div>
    </div>
</div>
