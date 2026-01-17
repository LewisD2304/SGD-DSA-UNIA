<?php

namespace App\Livewire\Documentos\Documento;

use App\Services\Documento\DocumentoService;
use App\Services\Documento\ArchivoDocumentoService;
use App\Services\Configuracion\AreaService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Responder extends Component
{
    use WithFileUploads;

    public $modeloDocumento = null;
    public $numeroDocumento = '';
    public $folioDocumento = '';
    public $asuntoDocumento = '';
    public $observacionDocumento = '';
    public $tipoDocumento = '';
    public $areaRemitente = '';
    public $oficina = '';
    public $areaDestino = '';
    public $archivosExistentes = [];
    public $comentarioDerivacion = null;

    // Campos para la respuesta
    public $idAreaRespuesta = '';
    public $comentarioRespuesta = '';
    public $archivosRespuesta = [];

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
        // Cargar áreas disponibles excluyendo el área actual
        $areaUsuario = Auth::user()->persona->id_area ?? null;
        $this->areas = $this->areaService->listarActivas()->filter(function($area) use ($areaUsuario) {
            return (int)$area->id_area !== (int)$areaUsuario;
        })->values();
    }

    public function abrirModal($id_documento)
    {
        $this->limpiarModal();

        // Cargar documento con todas sus relaciones
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento, [
            'tipoDocumento',
            'areaRemitente',
            'oficina',
            'areaDestino',
            'archivos',
            'ultimoComentarioMovimiento'
        ]);

        if (!$this->modeloDocumento) {
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

        // Cargar datos del documento (solo lectura)
        $this->numeroDocumento = $this->modeloDocumento->numero_documento;
        $this->folioDocumento = $this->modeloDocumento->folio_documento;
        $this->asuntoDocumento = $this->modeloDocumento->asunto_documento;
        $this->observacionDocumento = $this->modeloDocumento->observacion_documento;
        $this->tipoDocumento = $this->modeloDocumento->tipoDocumento->descripcion_catalogo ?? '';
        $this->areaRemitente = $this->modeloDocumento->areaRemitente->nombre_area ?? '';
        $this->oficina = ($this->modeloDocumento->oficina->abreviatura_catalogo ?? '') .
                        ($this->modeloDocumento->oficina ? ' - ' : '') .
                        ($this->modeloDocumento->oficina->descripcion_catalogo ?? '');
        $this->areaDestino = $this->modeloDocumento->areaDestino->nombre_area ?? '';
        $this->archivosExistentes = $this->modeloDocumento->archivos ?? collect();

        // Obtener el comentario del último movimiento
        // No mostrar si el documento está en estado OBSERVADO o OBSERVACION
        $nombreEstado = strtoupper($this->modeloDocumento->estado->nombre_estado ?? '');
        if (str_contains($nombreEstado, 'OBSERV')) {
            $this->comentarioDerivacion = null;
        } else {
            $ultimoComentario = $this->modeloDocumento->ultimoComentarioMovimiento;
            $this->comentarioDerivacion = ($ultimoComentario && !empty($ultimoComentario->comentario_documento))
                ? $ultimoComentario->comentario_documento
                : null;
        }

        $this->dispatch('cargando', cargando: 'false');
        $this->dispatch('modal', nombre: '#modal-responder-documento', accion: 'show');
    }

    public function guardarRespuesta()
    {
        $this->comentarioRespuesta = limpiarCadena($this->comentarioRespuesta);

        $reglas = [
            'idAreaRespuesta' => 'required|exists:ta_area,id_area',
            'comentarioRespuesta' => 'nullable|max:500',
        ];

        // Validar archivos si se adjuntaron
        if (!empty($this->archivosRespuesta)) {
            $reglas['archivosRespuesta'] = 'nullable|array|max:10';
            $reglas['archivosRespuesta.*'] = 'file|mimetypes:application/pdf,image/png,image/jpeg|max:10240';
        }

        $mensajes = [
            'idAreaRespuesta.required' => 'Debe seleccionar un área de destino',
            'idAreaRespuesta.exists' => 'El área seleccionada no existe',
            'comentarioRespuesta.max' => 'El comentario no puede exceder 500 caracteres',
            'archivosRespuesta.array' => 'Los archivos deben ser un conjunto válido.',
            'archivosRespuesta.max' => 'No puedes subir más de 10 archivos.',
            'archivosRespuesta.*.file' => 'Cada archivo debe ser un archivo válido.',
            'archivosRespuesta.*.mimetypes' => 'Solo se permiten archivos PDF, PNG o JPEG.',
            'archivosRespuesta.*.max' => 'Cada archivo no debe exceder 10MB.',
        ];

        $this->validate($reglas, $mensajes);

        $mensajeToastr = null;

        try {
            if (!$this->modeloDocumento) {
                throw new \Exception('No se encontró el documento');
            }

            // Buscar la transición DERIVAR desde el estado actual
            $transicion = \App\Models\Transicion::where('evento_transicion', 'DERIVAR')
                ->where('id_estado_actual_transicion', $this->modeloDocumento->id_estado)
                ->first();

            if (!$transicion) {
                throw new \Exception('No se puede responder el documento desde su estado actual');
            }

            // Procesar la transición (derivar al área de respuesta)
            $resultado = $this->documentoService->procesarTransicion(
                $this->modeloDocumento->id_documento,
                $transicion->id_transicion,
                [
                    'id_area_destino' => $this->idAreaRespuesta,
                    'observacion' => $this->comentarioRespuesta ?? 'Respuesta enviada',
                    'comentario_documento' => $this->comentarioRespuesta // Agregar el comentario para que se muestre en pendientes
                ]
            );

            // Guardar archivos de respuesta si existen
            if (!empty($this->archivosRespuesta)) {
                $usuario = Auth::user();
                $idAreaUsuario = $usuario->persona->id_area ?? null;

                $archivosInfo = $this->archivoService->guardarMultiplesArchivos(
                    archivos: $this->archivosRespuesta,
                    ruta: 'gestion/documentos/respuestas',
                    idDocumento: $this->modeloDocumento->id_documento,
                    idArea: $idAreaUsuario
                );

                // Guardar en BD con tipo de archivo 'respuesta'
                foreach ($archivosInfo as $info) {
                    \App\Models\ArchivoDocumento::create(array_merge($info, [
                        'tipo_archivo' => 'respuesta'
                    ]));
                }
            }

            $this->dispatch('refrescarDocumentos');
            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Respuesta enviada correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->dispatch('modal', nombre: '#modal-responder-documento', accion: 'hide');
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

    public function eliminarArchivoRespuesta($index)
    {
        if (isset($this->archivosRespuesta[$index])) {
            unset($this->archivosRespuesta[$index]);
            $this->archivosRespuesta = array_values($this->archivosRespuesta);
        }
    }

    public function limpiarModal()
    {
        $this->reset([
            'modeloDocumento',
            'numeroDocumento',
            'folioDocumento',
            'asuntoDocumento',
            'observacionDocumento',
            'tipoDocumento',
            'areaRemitente',
            'oficina',
            'areaDestino',
            'archivosExistentes',
            'idAreaRespuesta',
            'comentarioRespuesta',
            'comentarioDerivacion',
            'archivosRespuesta'
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.Documentos.documento.responder');
    }
}
