<?php

namespace App\Livewire\Components\Documentos\Pendientes;

use App\Services\Documento\DocumentoService;
use App\Models\Transicion;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Tabla extends Component
{
    use WithPagination;
    use WithFileUploads;

    #[Url('mostrar')]
    public $mostrarPaginate = 10;
    #[Url('buscar')]
    public $buscar = '';
    public $permisos = [];
    public ?int $documentoRecepcionId = null;
    public ?string $documentoRecepcionTitulo = null;
    public bool $esArchivar = false; // Para saber si es archivar o recepcionar
    public ?int $documentoRectificacionId = null;
    public string $accionRectificacion = '';
    public string $motivoRectificacion = '';

    // Propiedades para observar documento
    public $idAreaObservar = '';
    public $motivoObservacion = '';
    public $archivosEvidenciaObservacion = [];
    public $numeroDocumento = '';
    public $asuntoDocumento = '';
    public $areas = [];
    public $documentoObservarId = null;

    protected DocumentoService $documentoService;
    protected $paginationTheme = 'bootstrap';

    public function __construct()
    {
        $this->documentoService = resolve(DocumentoService::class);
    }

    #[Computed()]
    #[On('refrescarDocumentos')]
    #[On('refrescarDocumentosPendientes')]
    public function documentos()
    {
        $areaUsuario = Auth::user()?->persona?->id_area;

        if (!$areaUsuario) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->mostrarPaginate);
        }

        return $this->documentoService->listarPendientesPorArea(
            idArea: $areaUsuario,
            paginado: $this->mostrarPaginate,
            buscar: $this->buscar,
            columnaOrden: 'au_fechacr',
            orden: 'desc',
            relaciones: ['area', 'tipoDocumento', 'estado', 'areaRemitente', 'areaDestino', 'movimientos.areaOrigen', 'movimientos.areaDestino', 'ultimoComentarioMovimiento']
        );
    }

    public function solicitarRecepcion(int $id_documento, bool $archivar = false)
    {
        $documento = $this->documentoService->obtenerPorId($id_documento);

        if (!$documento) {
            return;
        }

        $this->documentoRecepcionId = $documento->id_documento;
        $this->documentoRecepcionTitulo = $documento->asunto_documento ?? $documento->expediente_documento ?? 'Documento sin asunto';

        // Verificar si el usuario actual es el área creadora original (Mesa de Partes)
        $areaUsuario = Auth::user()->persona->id_area ?? null;
        $esCreadorOriginal = ($documento->id_area_remitente == $areaUsuario &&
                             $documento->id_area_destino == $areaUsuario);

        // Solo se permite archivar si es Mesa de Partes y se solicitó archivar explícitamente
        $this->esArchivar = ($archivar && $esCreadorOriginal);

        $this->dispatch('modal', nombre: '#modal-confirmar-recepcion', accion: 'show');
    }

    public function confirmarRecepcion()
    {
        if (!$this->documentoRecepcionId) {
            return;
        }

        $documento = $this->documentoService->obtenerPorId($this->documentoRecepcionId);

        if (!$documento) {
            $this->cerrarModalRecepcion();
            return;
        }

        try {
            $areaUsuario = Auth::user()->persona->id_area ?? null;
            $esCreadorOriginal = ($documento->id_area_remitente == $areaUsuario &&
                                  $documento->id_area_destino == $areaUsuario);

            $archivar = $this->esArchivar && $esCreadorOriginal;

            // Si es archivar (Mesa de Partes tras haber recepcionado)
            if ($archivar) {
                // Buscar la transición de archivado (acepta alias ARCHIVAR/ARCHIVADO)
                $transicion = Transicion::whereIn('evento_transicion', ['ARCHIVADO', 'ARCHIVAR'])
                    ->where('id_estado_actual_transicion', $documento->id_estado)
                    ->first();

                $mensajeExito = 'Documento archivado correctamente';
            } else {
                // Si está OBSERVADO, usar transición específica OBSERVACION RECEPCIONADO
                $nombreEstado = strtoupper(optional($documento->estado)->nombre_estado);
                if ($nombreEstado === 'OBSERVADO') {
                    $transicion = Transicion::where('evento_transicion', 'OBSERVACION RECEPCIONADO')
                        ->where('id_estado_actual_transicion', $documento->id_estado)
                        ->first();
                } else if ($nombreEstado === 'SUBSANADO' || $nombreEstado === 'RECEPCION SUBSANADA') {
                    $transicion = Transicion::where('evento_transicion', 'RECEPCIONAR SUBSANACION')
                        ->where('id_estado_actual_transicion', $documento->id_estado)
                        ->first();
                } else {
                    $transicion = Transicion::where('evento_transicion', 'RECEPCIONAR')
                        ->where('id_estado_actual_transicion', $documento->id_estado)
                        ->first();
                }

                $mensajeExito = 'Documento recepcionado correctamente';
            }

            if (!$transicion) {
                $this->dispatch(
                    'toastr',
                    boton_cerrar: false,
                    progreso_avance: true,
                    duracion: '5000',
                    titulo: 'Error',
                    tipo: 'error',
                    mensaje: 'No se puede ' . ($archivar ? 'archivar' : 'recepcionar') . ' el documento en su estado actual',
                    posicion_y: 'top',
                    posicion_x: 'right'
                );
                $this->cerrarModalRecepcion();
                return;
            }

            // Usar procesarTransicion
            $this->documentoService->procesarTransicion(
                $documento->id_documento,
                $transicion->id_transicion,
                []
            );

            $this->dispatch('refrescarDocumentos');
            $this->dispatch('refrescarDocumentosPendientes');
            $this->dispatch(
                'toastr',
                boton_cerrar: false,
                progreso_avance: true,
                duracion: '3000',
                titulo: 'Éxito',
                tipo: 'success',
                mensaje: $mensajeExito,
                posicion_y: 'top',
                posicion_x: 'right'
            );
        } catch (\Exception $e) {
            $this->dispatch(
                'toastr',
                boton_cerrar: false,
                progreso_avance: true,
                duracion: '5000',
                titulo: 'Error',
                tipo: 'error',
                mensaje: $e->getMessage(),
                posicion_y: 'top',
                posicion_x: 'right'
            );
        }

        $this->cerrarModalRecepcion();
    }

    public function cerrarModalRecepcion()
    {
        $this->dispatch('modal', nombre: '#modal-confirmar-recepcion', accion: 'hide');
        $this->reset(['documentoRecepcionId', 'documentoRecepcionTitulo']);
    }

    public function abrirRectificacion(int $id_documento, string $accion)
    {
        Gate::authorize('autorizacion', ['RECTIFICAR', 'DOCUMENTOS']);

        $this->documentoRectificacionId = $id_documento;
        $this->accionRectificacion = $accion;
        $this->motivoRectificacion = '';

        // Marcar solicitud como vista registrando fecha_recepcion en el último movimiento 10
        $documento = $this->documentoService->obtenerPorId($id_documento);
        $idAreaUsuario = Auth::user()->persona->id_area ?? 0;
        if ($documento && $idAreaUsuario) {
            $this->documentoService->marcarSolicitudRectificacionVista($documento, $idAreaUsuario);
        }

        $this->dispatch('modal', nombre: '#modal-rectificacion', accion: 'show');
    }

    public function confirmarRectificacion()
    {
        Gate::authorize('autorizacion', ['RECTIFICAR', 'DOCUMENTOS']);

        if (!$this->documentoRectificacionId) {
            return;
        }

        $reglas = ['motivoRectificacion' => 'nullable|string|max:500'];

        if ($this->accionRectificacion === 'rechazar') {
            $reglas['motivoRectificacion'] = 'required|string|max:500';
        }

        $this->validate($reglas, [
            'motivoRectificacion.required' => 'Debe ingresar un motivo',
            'motivoRectificacion.max' => 'El motivo no puede exceder 500 caracteres'
        ]);

        $documento = $this->documentoService->obtenerPorId($this->documentoRectificacionId);

        if (!$documento) {
            $this->cerrarModalRectificacion();
            return;
        }

        try {
            $this->documentoService->resolverSolicitudRectificacion(
                $documento,
                $this->accionRectificacion,
                $this->motivoRectificacion ?: null
            );

            $this->dispatch('refrescarDocumentos');
            $this->dispatch('refrescarDocumentosPendientes');

            $mensaje = $this->accionRectificacion === 'aceptar'
                ? 'Solicitud de rectificación aceptada'
                : 'Solicitud de rectificación rechazada';

            $this->dispatch(
                'toastr',
                boton_cerrar: false,
                progreso_avance: true,
                duracion: '3000',
                titulo: 'Éxito',
                tipo: 'success',
                mensaje: $mensaje,
                posicion_y: 'top',
                posicion_x: 'right'
            );
        } catch (\Exception $e) {
            $this->dispatch(
                'toastr',
                boton_cerrar: false,
                progreso_avance: true,
                duracion: '5000',
                titulo: 'Error',
                tipo: 'error',
                mensaje: $e->getMessage(),
                posicion_y: 'top',
                posicion_x: 'right'
            );
        }

        $this->cerrarModalRectificacion();
    }

    public function cerrarModalRectificacion()
    {
        $this->dispatch('modal', nombre: '#modal-rectificacion', accion: 'hide');
        $this->reset(['documentoRectificacionId', 'accionRectificacion', 'motivoRectificacion']);
    }

    // Obtener transiciones disponibles para un documento según su estado actual
    public function obtenerTransicionesDisponibles($idEstado)
    {
        return Transicion::where('id_estado_actual_transicion', $idEstado)
            ->with(['estadoActual', 'estadoSiguiente'])
            ->get();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="row g-5 gx-xl-10 mb-5 mb-xl-10 animate__animated animate__fadeIn animate__faster">
            <div class="col-12">
                <div class="card">
                    <div class="d-flex flex-wrap flex-stack my-5 mx-8">
                        <div class="d-flex align-items-center position-relative my-1 me-4 fs-7">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input
                                type="text"
                                data-kt-user-table-filter="buscar"
                                class="form-control form-control-solid ps-13 w-xl-350px w-300"
                                placeholder="Buscar documento"
                                disabled
                            />
                        </div>

                        <div class="d-flex my-2">
                            <button
                                type="button"
                                class="btn btn-primary px-4 px-sm-6"
                                disabled
                            >
                                <i class="ki-outline ki-plus fs-2 px-0"></i>
                                <span class="d-none d-sm-inline">
                                    Nuevo
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body py-4">
                        <div lass="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                        <th class="w-10px pe-2">N°</th>
                                        <th class="min-w-150px">N° DOCUMENTO</th>
                                        <th class="min-w-250px">ASUNTO</th>
                                        <th class="min-w-125px">TIPO</th>
                                        <th class="min-w-125px">ÁREA</th>
                                        <th class="min-w-125px">FECHA RECEPCIÓN</th>
                                        <th class="min-w-100px">ESTADO</th>
                                        <th class="text-center min-w-100px">ACCIONES</th>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold placeholder-glow">
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function mount()
    {
        $menuService = resolve(\App\Services\Seguridad\MenuService::class);
        $menu = $menuService->listarAccionesPorNombreMenu('DOCUMENTOS');

        if ($menu) {
            foreach ($menu->acciones as $accion) {
                $nombre_accion = str_replace(' ', '_', strtoupper($accion->tipoAccion->descripcion_catalogo));
                if ($nombre_accion !== 'LISTAR') {
                    $this->permisos[$nombre_accion] = Gate::allows('autorizacion', [$nombre_accion, $menu->nombre_menu]);
                }
            }
        }
    }

    #[On('abrirModalObservarDocumento')]
    public function abrirModalObservarDocumento($id_documento)
    {
        $areaService = resolve(\App\Services\Configuracion\AreaService::class);
        $areaUsuario = Auth::user()->persona->id_area ?? null;

        // Obtener todas las áreas activas excepto la del usuario actual
        $todasLasAreas = $areaService->listarActivas();
        $this->areas = $todasLasAreas->filter(function ($area) use ($areaUsuario) {
            return (int) $area->id_area !== (int) $areaUsuario;
        })->values()->toArray();

        $documento = $this->documentoService->obtenerPorId($id_documento);

        if ($documento) {
            $this->documentoObservarId = $id_documento;
            $this->numeroDocumento = $documento->expediente_documento ?? 'N/A';
            $this->asuntoDocumento = $documento->asunto_documento ?? 'Sin asunto';
        }

        $this->dispatch('modal', nombre: '#modal-observacion-documento', accion: 'show');
    }

    public function guardarObservacion()
    {
        $this->validate([
            'idAreaObservar' => 'required|exists:ta_area,id_area',
            'motivoObservacion' => 'required|min:10|max:500',
            'archivosEvidenciaObservacion' => 'nullable|array|max:10',
            'archivosEvidenciaObservacion.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'idAreaObservar.required' => 'Debe seleccionar un área',
            'motivoObservacion.required' => 'Debe indicar el motivo de la observación',
            'motivoObservacion.min' => 'El motivo debe tener al menos 10 caracteres',
        ]);

        // Aquí iría la lógica para guardar la observación
        // Similar a como lo haces en el componente Index de Documentos

        $this->dispatch('modal', nombre: '#modal-observacion-documento', accion: 'hide');
        $this->limpiarModalObservacion();

        $this->dispatch(
            'toastr',
            boton_cerrar: false,
            progreso_avance: true,
            duracion: '3000',
            titulo: 'Éxito',
            tipo: 'success',
            mensaje: 'Documento observado correctamente',
            posicion_y: 'top',
            posicion_x: 'right'
        );

        $this->dispatch('refrescarDocumentosPendientes');
    }

    public function quitarArchivoObservacion($index)
    {
        if (isset($this->archivosEvidenciaObservacion[$index])) {
            unset($this->archivosEvidenciaObservacion[$index]);
            $this->archivosEvidenciaObservacion = array_values($this->archivosEvidenciaObservacion);
        }
    }

    public function limpiarModalObservacion()
    {
        $this->reset([
            'idAreaObservar',
            'motivoObservacion',
            'archivosEvidenciaObservacion',
            'numeroDocumento',
            'asuntoDocumento',
            'documentoObservarId'
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.components.Documentos.pendientes.tabla');
    }
}
