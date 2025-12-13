<?php

namespace App\Livewire\Documentos\Documento;

use App\Enums\EstadoEnum;
use App\Services\Documento\DocumentoService;
use App\Services\Documento\ArchivoDocumentoService;
use App\Services\Configuracion\AreaService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;
    public $tituloModal = 'Registrar nuevo documento';
    public $modoModal = 1; // 1: Registrar, 2: Modificar, 1: Habilitar, 2: Deshabilitar
    public $modeloDocumento = null;
    public $nombreDocumentoEstado = '';
    public $nombreDocumentoEliminar = '';

    #[Validate('required|max:50|min:1', as: 'numero_documento')]
    public $numeroDocumento = '';

    #[Validate('required|max:50|min:1', as: 'folio_documento')]
    public $folioDocumento = '';

    #[Validate('required|max:200|min:3', as: 'asunto_documento')]
    public $asuntoDocumento = '';

    public $idAreaRemitente; // Se obtiene del usuario logueado

    #[Validate('required|exists:ta_area,id_area', as: 'área destino')]
    public $idAreaDestino = '';

    #[Validate('nullable|date', as: 'fecha_recepcion_documento')]
    public $fechaRecepcionDocumento = '';

    #[Validate('max:250', as: 'ruta_documento')]
    public $rutaDocumento = '';

    public $archivoDocumento = null;
    public $rutaActual = 'gestion.documentos.documentos';
    public $areas = [];

    protected DocumentoService $documentoService;
    protected ArchivoDocumentoService $archivoService;
    protected AreaService $areaService;

    public function __construct()
    {
        $this->documentoService = resolve(DocumentoService::class);
        $this->archivoService = resolve(ArchivoDocumentoService::class);
        $this->areaService = resolve(AreaService::class);
    }

    public function mount()
    {
        // Obtener área del usuario logueado
        $usuario = Auth::user();
        $this->idAreaRemitente = $usuario->persona->id_area ?? null;

        // Listar todas las áreas para el select de destino, EXCEPTO la del usuario
        $todasLasAreas = $this->areaService->listarActivas();
        $this->areas = $todasLasAreas->filter(function($area) {
            return $area->id_area !== $this->idAreaRemitente;
        })->values();
    }

    public function guardarDocumento()
    {
        $this->numeroDocumento = limpiarCadena($this->numeroDocumento, false);
        $this->folioDocumento = limpiarCadena($this->folioDocumento, false);
        $this->asuntoDocumento = limpiarCadena($this->asuntoDocumento, false);

        $mensajeToastr = null;

        try {
            $reglas = [
                'numeroDocumento' => [
                    'required',
                    'max:50',
                    Rule::unique('ta_documento', 'numero_documento')
                        ->ignore($this->modeloDocumento->id_documento ?? null, 'id_documento'),
                ],
                'folioDocumento' => 'required|max:50|min:1',
                'asuntoDocumento' => 'required|max:200|min:3',
                'idAreaDestino' => 'required|exists:ta_area,id_area',
                'fechaRecepcionDocumento' => 'nullable|date',
            ];

            // Validar archivo solo si se está creando o modificando con nuevo archivo
            if ($this->modoModal == 1 || $this->archivoDocumento) {
                $reglas['archivoDocumento'] = 'required|file|mimes:pdf,png,jpg,jpeg|max:10240'; // 10MB
            }

            $this->validate($reglas);

            if ($this->modoModal == 1) {
                $mensajeToastr = $this->registrar();
            } else {
                $mensajeToastr = $this->modificar();
            }
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalDocumento('#modal-documento', 'hide');
        $this->limpiarModal();

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

    public function registrar()
    {
        $rutaArchivo = null;

        // Guardar archivo físico si existe
        $nombreArchivoOriginal = null;
        if ($this->archivoDocumento) {
            $infoArchivo = $this->archivoService->guardarArchivo(
                archivo: $this->archivoDocumento,
                ruta: $this->rutaActual
            );

            if ($infoArchivo) {
                $rutaArchivo = $infoArchivo['ruta_archivo'];
                $nombreArchivoOriginal = $infoArchivo['nombre_archivo_original'];
            }
        }

        $this->documentoService->registrar([
            'numero_documento' => $this->numeroDocumento,
            'folio_documento' => $this->folioDocumento,
            'asunto_documento' => $this->asuntoDocumento,
            'id_area_remitente' => $this->idAreaRemitente,
            'id_area_destino' => $this->idAreaDestino,
            // Al crear, siempre queda pendiente: fecha de recepción en NULL hasta que el destino recepcione
            'fecha_recepcion_documento' => null,
            'ruta_documento' => $rutaArchivo,
            'nombre_archivo_original' => $nombreArchivoOriginal,
        ]);

        $this->dispatch('refrescarDocumentos');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento registrado correctamente', 'top', 'right');
    }

    public function modificar()
    {
        $datos = [
            'numero_documento' => $this->numeroDocumento,
            'folio_documento' => $this->folioDocumento,
            'asunto_documento' => $this->asuntoDocumento,
            'id_area_destino' => $this->idAreaDestino,
            'fecha_recepcion_documento' => $this->fechaRecepcionDocumento ?: null,
        ];

        // Si hay un nuevo archivo, modificarlo
        if ($this->archivoDocumento) {
            $infoArchivo = $this->archivoService->modificarArchivo(
                archivo: $this->archivoDocumento,
                ruta: $this->rutaActual,
                rutaAnterior: $this->modeloDocumento->ruta_documento
            );

            if ($infoArchivo) {
                $datos['ruta_documento'] = $infoArchivo['ruta_archivo'];
                $datos['nombre_archivo_original'] = $infoArchivo['nombre_archivo_original'];
            }
        }

        $this->documentoService->modificar($datos, $this->modeloDocumento);

        $this->dispatch('refrescarDocumentos');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento modificado correctamente', 'top', 'right');
    }

    #[On('abrirModalDocumento')]
    public function abrirModalDocumento($id_documento = null)
    {
        $this->limpiarModal();

        if (!is_null($id_documento)) {
            $this->tituloModal = 'Modificar documento';
            $this->modoModal = 2;
            $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento);
            $this->numeroDocumento = $this->modeloDocumento->numero_documento;
            $this->folioDocumento = $this->modeloDocumento->folio_documento;
            $this->asuntoDocumento = $this->modeloDocumento->asunto_documento;
            $this->idAreaDestino = $this->modeloDocumento->id_area_destino;
            $this->fechaRecepcionDocumento = $this->modeloDocumento->fecha_recepcion_documento;
            $this->rutaDocumento = $this->modeloDocumento->ruta_documento;
        } else {
            $this->tituloModal = 'Registrar nuevo documento';
            $this->modoModal = 1;
        }

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-documento', 'show');
    }

    public function cambiarEstadoDocumento()
    {
        $mensajeToastr = null;

        try {
            $this->documentoService->modificar([
                'id_estado' => $this->modoModal === 1 ? EstadoEnum::HABILITADO->value : EstadoEnum::DESHABILITADO->value,
            ], $this->modeloDocumento);

            $this->dispatch('refrescarDocumentos');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento ' . ($this->modoModal == 1 ? 'habilitado' : 'deshabilitado') . ' correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalDocumento('#modal-estado-documento', 'hide');
        $this->limpiarModal();

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

    #[On('abrirModalEstadoDocumento')]
    public function abrirModalEstadoDocumento($id_documento)
    {
        $this->limpiarModal();
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento);
        $this->nombreDocumentoEstado = $this->modeloDocumento->numero_documento;
        $this->modoModal = ($this->modeloDocumento->id_estado ?? EstadoEnum::HABILITADO->value) === EstadoEnum::HABILITADO->value ? 2 : 1;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-estado-documento', 'show');
    }

    public function eliminarDocumento()
    {
        $mensajeToastr = null;

        try {
            $this->documentoService->eliminar($this->modeloDocumento, ['movimientos']);

            $this->dispatch('refrescarDocumentos');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento eliminado correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalDocumento('#modal-eliminar-documento', 'hide');
        $this->limpiarModal();

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

    #[On('abrirModalEliminarDocumento')]
    public function abrirModalEliminarDocumento($id_documento)
    {
        $this->limpiarModal();
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento);
        $this->nombreDocumentoEliminar = $this->modeloDocumento->numero_documento;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-eliminar-documento', 'show');
    }

    #[On('abrirModalDetalleDocumento')]
    public function abrirModalDetalleDocumento($id_documento)
    {
        $this->limpiarModal();
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento);

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-detalle-documento', 'show');
    }

    public function modalDocumento($nombre, $accion)
    {
        $this->dispatch(
            'modal',
            nombre: $nombre,
            accion: $accion
        );
    }

    public function limpiarModal()
    {
        $this->tituloModal = 'Registrar nuevo documento';
        $this->modoModal = 1;
        $this->reset([
            'modeloDocumento',
            'numeroDocumento',
            'folioDocumento',
            'asuntoDocumento',
            'idAreaDestino',
            'fechaRecepcionDocumento',
            'rutaDocumento',
            'archivoDocumento',
            'nombreDocumentoEliminar',
            'nombreDocumentoEstado'
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.documentos.documento.index');
    }
}
