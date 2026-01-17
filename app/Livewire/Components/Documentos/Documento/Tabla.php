<?php

namespace App\Livewire\Components\Documentos\Documento;

use App\Models\Estado;
use App\Services\Documento\DocumentoService;
use App\Services\Configuracion\AreaService;
use App\Services\Seguridad\MenuService;
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
    #[Url('fecha_inicio')]
    public $fechaInicio = '';
    #[Url('fecha_fin')]
    public $fechaFin = '';
    #[Url('estado')]
    public $idEstadoFiltro = '';
    #[Url('oficina')]
    public $idOficinaCatalogo = '';
    public $permisos = [];
    public $estados = [];
    public $oficinas = [];
    public ?int $documentoArchivarId = null;
    public ?string $documentoArchivarTitulo = null;
    public ?int $documentoObservarId = null;
    public ?string $documentoObservarTitulo = null;
    public $motivoObservacion = '';
    public $archivosEvidenciaObservacion = [];
    public $idAreaObservar = '';
    public $areas = [];
    public ?string $numeroDocumento = null;
    public ?string $folioDocumento = null;
    public ?string $asuntoDocumento = null;
    public $modeloDocumento = null;

    protected DocumentoService $documentoService;
    protected AreaService $areaService;
    protected $paginationTheme = 'bootstrap';

    public function __construct()
    {
        $this->documentoService = resolve(DocumentoService::class);
        $this->areaService = resolve(AreaService::class);
    }

    public function mount()
    {
        $catalogoService = resolve(\App\Services\Configuracion\Catalogo\CatalogoService::class);
        $idPadreOficinas = null;
        try {
            $idPadreOficinas = $catalogoService->obtenerIdPorNombre('OFICINAS');
        } catch (\Throwable $e) {
            $padre = $catalogoService->buscarPadre('OF');
            $idPadreOficinas = $padre?->id_catalogo;
        }
        if ($idPadreOficinas) {
            $this->oficinas = $catalogoService->listarHijos($idPadreOficinas, [], 0);
        }

        // Cargar todos los estados para el filtro
        $this->estados = Estado::all()->pluck('nombre_estado', 'id_estado');

        $menuService = resolve(MenuService::class);
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

    #[Computed()]
    #[On('refrescarDocumentos')]
    public function documentos()
    {
        $areaUsuario = Auth::user()?->persona?->id_area;

        if (!$areaUsuario) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->mostrarPaginate);
        }

        return $this->documentoService->listarPorAreaConFiltros(
            idArea: $areaUsuario,
            paginado: $this->mostrarPaginate,
            buscar: $this->buscar,
            fechaInicio: $this->fechaInicio,
            fechaFin: $this->fechaFin,
            idEstado: $this->idEstadoFiltro,
            idOficinaCatalogo: !empty($this->idOficinaCatalogo) ? $this->idOficinaCatalogo : null,
            columnaOrden: 'au_fechacr',
            orden: 'desc',
            relaciones: ['area', 'tipoDocumento', 'oficina', 'estado', 'areaRemitente', 'areaDestino', 'movimientos.areaOrigen']
        );
    }

    public function abrirModalArchivar(int $id_documento): void
    {
        $documento = $this->documentoService->obtenerPorId($id_documento);

        if (!$documento) {
            $this->dispatch('toastr',
                boton_cerrar: false,
                progreso_avance: true,
                duracion: '5000',
                titulo: 'Error',
                tipo: 'error',
                mensaje: 'Documento no encontrado',
                posicion_y: 'top',
                posicion_x: 'right'
            );
            return;
        }

        $this->documentoArchivarId = $documento->id_documento;
        $this->documentoArchivarTitulo = $documento->asunto_documento ?? $documento->expediente_documento;

        $this->dispatch('modal', nombre: '#modal-archivar-documento', accion: 'show');
    }

    public function confirmarArchivar(): void
    {
        $mensajeToastr = null;

        if (!$this->documentoArchivarId) {
            return;
        }

        try {
            $documento = $this->documentoService->obtenerPorId($this->documentoArchivarId);

            if (!$documento) {
                throw new \Exception('Documento no encontrado');
            }

            $areaUsuario = Auth::user()->persona->id_area ?? null;

            // Solo el área destino actual puede archivar
            if (!$areaUsuario || $documento->id_area_destino != $areaUsuario) {
                throw new \Exception('No tiene permisos para archivar este documento');
            }

            $estadoActual = strtoupper(optional($documento->estado)->nombre_estado);

            if ($estadoActual !== 'RECEPCIONADO') {
                throw new \Exception('Solo se pueden archivar documentos recepcionados');
            }

            $transicion = \App\Models\Transicion::whereIn('evento_transicion', ['ARCHIVADO', 'ARCHIVAR'])
                ->where('id_estado_actual_transicion', $documento->id_estado)
                ->first();

            if (!$transicion) {
                throw new \Exception('No se encontró transición para archivar');
            }

            $this->documentoService->procesarTransicion(
                $documento->id_documento,
                $transicion->id_transicion,
                []
            );

            $this->dispatch('refrescarDocumentos');
            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento archivado correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->dispatch('modal', nombre: '#modal-archivar-documento', accion: 'hide');
        $this->reset(['documentoArchivarId', 'documentoArchivarTitulo']);

        if ($mensajeToastr !== null) {
            $this->dispatch(
                'toastr',
                boton_cerrar: $mensajeToastr['boton_cerrar'],
                progreso_avance: $mensajeToastr['progreso_avance'],
                duracion: $mensajeToastr['duracion'],
                titulo: $mensajeToastr['titulo'],
                tipo: $mensajeToastr['tipo'],
                mensaje: $mensajeToastr['mensaje'],
                posicion_y: $mensajeToastr['posicion_y'],
                posicion_x: $mensajeToastr['posicion_x']
            );
        }
    }

    #[On('abrirModalObservarDocumento')]
    public function abrirModalObservarDocumento(int $id_documento): void
    {
        $documento = $this->documentoService->obtenerPorId($id_documento, ['estado']);

        if (!$documento) {
            $this->dispatch('toastr',
                boton_cerrar: false,
                progreso_avance: true,
                duracion: '5000',
                titulo: 'Error',
                tipo: 'error',
                mensaje: 'Documento no encontrado',
                posicion_y: 'top',
                posicion_x: 'right'
            );
            return;
        }

        // Cargar las áreas disponibles
        $areaUsuario = Auth::user()->persona->id_area ?? null;
        $todasLasAreas = $this->areaService->listarActivas();
        $this->areas = $todasLasAreas->filter(function ($area) use ($areaUsuario) {
            return (int) $area->id_area !== (int) $areaUsuario;
        })->values();

        $this->documentoObservarId = $documento->id_documento;
        $this->documentoObservarTitulo = $documento->asunto_documento ?? $documento->expediente_documento;
        $this->numeroDocumento = $documento->numero_documento;
        $this->folioDocumento = $documento->folio_documento;
        $this->asuntoDocumento = $documento->asunto_documento;
        $this->modeloDocumento = $documento;
        $this->motivoObservacion = '';
        $this->archivosEvidenciaObservacion = [];
        $this->idAreaObservar = $documento->id_area_destino ?? '';

        $this->dispatch('modal', nombre: '#modal-observacion-documento', accion: 'show');
    }

    public function guardarObservacion(): void
    {
        $this->motivoObservacion = limpiarCadena($this->motivoObservacion);

        $reglas = [
            'motivoObservacion' => 'required|max:500',
            'idAreaObservar' => 'required|exists:ta_area,id_area',
            'archivosEvidenciaObservacion' => 'nullable|array',
            'archivosEvidenciaObservacion.*' => 'file|mimetypes:application/pdf,image/png,image/jpeg|max:10240'
        ];

        $this->validate($reglas, [
            'motivoObservacion.required' => 'El motivo de la observación es obligatorio',
            'motivoObservacion.max' => 'El motivo de la observación no puede exceder 500 caracteres'
        ]);

        $mensajeToastr = null;

        try {
            if (!$this->documentoObservarId) {
                throw new \Exception('Documento no encontrado');
            }

            $documento = $this->documentoService->obtenerPorId($this->documentoObservarId);

            if (!$documento) {
                throw new \Exception('Documento no encontrado');
            }

            // Procesar transición OBSERVAR (estado actual a estado OBSERVADO)
            $transicion = \App\Models\Transicion::where('evento_transicion', 'OBSERVAR')
                ->where('id_estado_actual_transicion', $documento->id_estado)
                ->first();

            if (!$transicion) {
                throw new \Exception('No se encontró la transición para observar el documento');
            }

            // Procesar la transición
            $this->documentoService->procesarTransicion(
                $documento->id_documento,
                $transicion->id_transicion,
                [
                    'observacion' => $this->motivoObservacion,
                    'id_area_destino' => $this->idAreaObservar
                ]
            );

            // Guardar archivos de evidencia si existen
            if (!empty($this->archivosEvidenciaObservacion)) {
                $usuario = Auth::user();
                $idAreaUsuario = $usuario->persona->id_area ?? null;
                $archivoService = resolve(\App\Services\Documento\ArchivoDocumentoService::class);
                $archivosInfo = $archivoService->guardarMultiplesArchivos(
                    archivos: $this->archivosEvidenciaObservacion,
                    ruta: 'gestion/documentos/evidencias_observacion',
                    idDocumento: $documento->id_documento,
                    idArea: $idAreaUsuario
                );

                foreach ($archivosInfo as $info) {
                    \App\Models\ArchivoDocumento::create(array_merge($info, [
                        'tipo_archivo' => 'evidencia_observacion'
                    ]));
                }
            }

            $this->dispatch('refrescarDocumentos');
            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento observado correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->dispatch('modal', nombre: '#modal-observacion-documento', accion: 'hide');
        $this->reset(['documentoObservarId', 'documentoObservarTitulo', 'motivoObservacion', 'archivosEvidenciaObservacion', 'numeroDocumento', 'folioDocumento', 'asuntoDocumento', 'modeloDocumento', 'idAreaObservar']);

        if ($mensajeToastr !== null) {
            $this->dispatch(
                'toastr',
                boton_cerrar: $mensajeToastr['boton_cerrar'],
                progreso_avance: $mensajeToastr['progreso_avance'],
                duracion: $mensajeToastr['duracion'],
                titulo: $mensajeToastr['titulo'],
                tipo: $mensajeToastr['tipo'],
                mensaje: $mensajeToastr['mensaje'],
                posicion_y: $mensajeToastr['posicion_y'],
                posicion_x: $mensajeToastr['posicion_x']
            );
        }
    }

    public function quitarArchivoObservacion(int $index): void
    {
        if (isset($this->archivosEvidenciaObservacion[$index])) {
            unset($this->archivosEvidenciaObservacion[$index]);
            $this->archivosEvidenciaObservacion = array_values($this->archivosEvidenciaObservacion);
        }
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
                                    </tbody>
                                </table>
                                <div class="position-absolute top-50 start-50 translate-middle" style="margin-top: 1.06rem;">
                                    <span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function limpiarFiltros()
    {
        $this->reset(['buscar', 'fechaInicio', 'fechaFin', 'idEstadoFiltro', 'idOficinaCatalogo']);
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.components.documentos.Documento.tabla');
    }
}
