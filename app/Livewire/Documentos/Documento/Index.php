<?php

namespace App\Livewire\Documentos\Documento;

use App\Enums\EstadoEnum;
use App\Models\ArchivoDocumento;
use App\Models\Transicion;
use App\Services\Documento\DocumentoService;
use App\Services\Documento\ArchivoDocumentoService;
use App\Services\Configuracion\AreaService;
use App\Services\Configuracion\Catalogo\CatalogoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;
    public $tituloModal = 'Registrar nuevo documento';
    public $modoModal = 1; // 1: Registrar, 2: Modificar, 1: Habilitar, 2: Deshabilitar
    public $modeloDocumento = null;
    public $nombreDocumentoEstado = '';
    public $nombreDocumentoEliminar = '';

    #[Validate('required|max:100|min:1', as: 'numero_documento')]
    public $numeroDocumento = '';

    public $folioDocumento = '';

    #[Validate('required|max:200|min:3', as: 'asunto_documento')]
    public $asuntoDocumento = '';

    #[Validate('required|max:200|min:3', as: 'observacion_documento')]
    public $observacionDocumento = '';

    public $idAreaRemitente; // Se obtiene del usuario logueado

    #[Validate('required|exists:ta_area,id_area', as: 'área destino')]
    public $idAreaDestino = '';

    #[Validate('required|exists:ta_catalogo,id_catalogo', as: 'tipo de documento')]
    public $tipoDocumentoCatalogo = '';

    #[Validate('max:250', as: 'ruta_documento')]

    public $archivosDocumento = [];
    public $archivosExistentes = [];
    public $rutaActual = 'gestion.documentos.documentos';
    public $areas = [];
    public $tiposDocumento = [];

    // Propiedades para derivar documento
    public $idAreaDerivar = '';
    public $observacionesDerivar = '';

    protected DocumentoService $documentoService;
    protected ArchivoDocumentoService $archivoService;
    protected AreaService $areaService;
    protected CatalogoService $catalogoService;

    public function __construct()
    {
        $this->documentoService = resolve(DocumentoService::class);
        $this->archivoService = resolve(ArchivoDocumentoService::class);
        $this->areaService = resolve(AreaService::class);
        $this->catalogoService = resolve(CatalogoService::class);
    }

    public function mount()
    {
        // Obtener área del usuario logueado
        $usuario = Auth::user();
        // Casteamos a entero para evitar problemas de comparación estricta (string vs int)
        $this->idAreaRemitente = (int) ($usuario->persona->id_area ?? 0);

        // Listar todas las áreas para el select de destino, EXCEPTO la del usuario
        $todasLasAreas = $this->areaService->listarActivas();
        $this->areas = $todasLasAreas->filter(function ($area) {
            return (int) $area->id_area !== $this->idAreaRemitente;
        })->values();

        // Obtener tipos de documento del catálogo (hijos de TIPO DOCUMENTO)
        $tipoDocumentoPadre = $this->catalogoService->buscarPadre('TD');
        if ($tipoDocumentoPadre) {
            $this->tiposDocumento = $this->catalogoService->listarHijos($tipoDocumentoPadre->id_catalogo, [], 0);
        }
    }

    public function guardarDocumento()
    {
        $this->numeroDocumento = limpiarCadena($this->numeroDocumento, false);
        $this->folioDocumento = limpiarCadena($this->folioDocumento, false);
        $this->asuntoDocumento = limpiarCadena($this->asuntoDocumento, false);
        $this->observacionDocumento = limpiarCadena($this->observacionDocumento, false);

        $mensajeToastr = null;

        try {
            // ==============================================================================
            // 1. SOLUCIÓN AL ERROR SQL: ASIGNAR REMITENTE AUTOMÁTICAMENTE
            // ==============================================================================
            // Obtenemos el área del usuario logueado.
            // Usamos el operador ?? null por si la relación persona o area no existe.
            $areaUsuario = Auth::user()->persona->id_area ?? null;

            // Validamos que el usuario tenga un área antes de continuar.
            if (!$areaUsuario) {
                throw new \Exception('Su usuario no tiene un Área asignada, por lo tanto no puede registrar documentos.');
            }

            // Asignamos el valor a la propiedad del componente para que se guarde en la BD.
            $this->idAreaRemitente = $areaUsuario;


            // ==============================================================================
            // 2. LÓGICA DE VALIDACIÓN (Tu código original continúa aquí)
            // ==============================================================================
            $tieneArchivos = !empty($this->archivosDocumento) || !empty($this->archivosExistentes);

            $reglaFolio = $tieneArchivos ? 'required|numeric|min:1|max:999999' : 'nullable|numeric|min:1|max:999999';

            $reglas = [
                'numeroDocumento' => [
                    'required',
                    'max:50',
                    Rule::unique('ta_documento', 'numero_documento')
                        ->ignore($this->modeloDocumento->id_documento ?? null, 'id_documento'),
                ],
                'folioDocumento' => $reglaFolio,
                'asuntoDocumento' => 'required|max:200|min:3',
                'idAreaDestino' => 'required|exists:ta_area,id_area',
                'tipoDocumentoCatalogo' => 'required|exists:ta_catalogo,id_catalogo',
                'observacionDocumento' => 'nullable|max:500',
            ];

            // Validar archivos (múltiples) solo si se está creando o modificando con nuevos archivos
            if ($this->modoModal == 1 || !empty($this->archivosDocumento)) {
                $reglas['archivosDocumento'] = 'nullable|array';
                $reglas['archivosDocumento.*'] = 'file|mimes:pdf,png,jpg,jpeg|max:10240'; // Solo PDF e imágenes, 10MB cada uno
            }

            $mensajes = [
                'folioDocumento.required' => 'El campo folio es obligatorio cuando se adjuntan archivos.',
                'folioDocumento.numeric' => 'El campo folio debe ser un número.',
                'folioDocumento.min' => 'El campo folio debe ser al menos 1.',
                'folioDocumento.max' => 'El campo folio no puede ser mayor a 999999.',
            ];

            $this->validate($reglas, $mensajes);

            if ($this->modoModal == 1) {
                $mensajeToastr = $this->registrar();
            } else {
                $mensajeToastr = $this->modificar();
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
        } catch (ValidationException $e) {

            $this->setErrorBag($e->validator->getMessageBag());
            $this->dispatch('errores_validacion', validacion: $this->getErrorBag()->messages());
        } catch (\Exception $e) {
            // CAPTURAR OTROS ERRORES (BD, Lógica)
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
    }

    public function registrar()
    {
        // Registrar documento primero
        $documento = $this->documentoService->registrar([
            'numero_documento' => $this->numeroDocumento,
            'folio_documento' => !empty($this->folioDocumento) ? $this->folioDocumento : null,
            'asunto_documento' => $this->asuntoDocumento,
            'observacion_documento' => $this->observacionDocumento,
            'id_area_remitente' => $this->idAreaRemitente,
            'id_area_destino' => $this->idAreaDestino,
            'tipo_documento_catalogo' => $this->tipoDocumentoCatalogo,
            'fecha_recepcion_documento' => null,
        ]);

        // Guardar archivos adjuntos si existen
        if (!empty($this->archivosDocumento)) {
            $archivosInfo = $this->archivoService->guardarMultiplesArchivos(
                archivos: $this->archivosDocumento,
                ruta: $this->rutaActual,
                idDocumento: $documento->id_documento,
                idArea: $this->idAreaRemitente
            );

            // Guardar en BD
            foreach ($archivosInfo as $archivoInfo) {
                ArchivoDocumento::create($archivoInfo);
            }
        }

        $this->dispatch('refrescarDocumentos');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento registrado correctamente', 'top', 'right');
    }

    public function modificar()
    {
        $datos = [
            'numero_documento' => $this->numeroDocumento,
            'folio_documento' => !empty($this->folioDocumento) ? $this->folioDocumento : null,
            'asunto_documento' => $this->asuntoDocumento,
            'observacion_documento' => $this->observacionDocumento,
            'id_area_destino' => $this->idAreaDestino,
            'tipo_documento_catalogo' => $this->tipoDocumentoCatalogo,
        ];

        // Modificar documento
        $this->documentoService->modificar($datos, $this->modeloDocumento);

        // Guardar nuevos archivos si existen
        if (!empty($this->archivosDocumento)) {
            // Obtener el orden máximo actual
            $ordenMaximo = ArchivoDocumento::where('id_documento', $this->modeloDocumento->id_documento)
                ->max('orden') ?? 0;

            $archivosInfo = $this->archivoService->guardarMultiplesArchivos(
                archivos: $this->archivosDocumento,
                ruta: $this->rutaActual,
                idDocumento: $this->modeloDocumento->id_documento,
                idArea: $this->idAreaRemitente
            );

            // Actualizar orden desde el último existente
            foreach ($archivosInfo as $index => $archivoInfo) {
                $archivoInfo['orden'] = $ordenMaximo + $index + 1;
                ArchivoDocumento::create($archivoInfo);
            }
        }

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
            $this->modeloDocumento = $this->documentoService->obtenerPorIdParaArea($id_documento, $this->idAreaRemitente, ['archivos']);
            $this->numeroDocumento = $this->modeloDocumento->numero_documento;
            $this->folioDocumento = $this->modeloDocumento->folio_documento;
            $this->tipoDocumentoCatalogo = $this->modeloDocumento->tipo_documento_catalogo;
            $this->asuntoDocumento = $this->modeloDocumento->asunto_documento;
            $this->observacionDocumento = $this->modeloDocumento->observacion_documento;
            $this->idAreaDestino = $this->modeloDocumento->id_area_destino;
            $this->archivosExistentes = $this->modeloDocumento->archivos ?? collect();
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
        // En "Mis Documentos" NO incluir archivos de derivaciones (false por defecto)
        // Solo se ven los archivos originales hasta que se archive desde Pendientes
        $this->modeloDocumento = $this->documentoService->obtenerPorIdParaArea(
            $id_documento,
            $this->idAreaRemitente,
            ['estado', 'tipoDocumento', 'archivos'],
            false // No incluir derivaciones
        );

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
            'observacionDocumento',
            'idAreaDestino',
            'tipoDocumentoCatalogo',
            'archivosDocumento',
            'archivosExistentes',
            'nombreDocumentoEliminar',
            'nombreDocumentoEstado',
            'idAreaDerivar',
            'observacionesDerivar'
        ]);
        $this->resetErrorBag();
    }

    public function eliminarArchivo($index)
    {
        if (isset($this->archivosDocumento[$index])) {
            unset($this->archivosDocumento[$index]);
            $this->archivosDocumento = array_values($this->archivosDocumento);
        }
    }

    public function eliminarArchivoExistente($idArchivo)
    {
        try {
            $archivo = \App\Models\ArchivoDocumento::find($idArchivo);
            if ($archivo) {
                // Eliminar archivo físico
                $this->archivoService->eliminarArchivo($archivo->ruta_archivo);
                // Eliminar registro de BD
                $archivo->delete();

                // Actualizar lista
                $this->archivosExistentes = collect($this->archivosExistentes)->reject(fn($item) => $item->id_archivo_documento == $idArchivo)->values()->all();

                $this->dispatch(
                    'toastr',
                    boton_cerrar: false,
                    progreso_avance: true,
                    duracion: '3000',
                    titulo: 'Éxito',
                    tipo: 'success',
                    mensaje: 'Archivo eliminado correctamente',
                    posicion_y: 'top',
                    posicion_x: 'right'
                );
            }
        } catch (\Exception $e) {
            $this->dispatch(
                'toastr',
                boton_cerrar: false,
                progreso_avance: true,
                duracion: '5000',
                titulo: 'Error',
                tipo: 'error',
                mensaje: 'Error al eliminar archivo: ' . $e->getMessage(),
                posicion_y: 'top',
                posicion_x: 'right'
            );
        }
    }

    #[On('abrirModalDerivarDocumento')]
    public function abrirModalDerivarDocumento($id_documento)
    {
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento, ['tipoDocumento', 'areaRemitente', 'areaDestino', 'estado']);

        $this->numeroDocumento = $this->modeloDocumento->numero_documento;
        $this->folioDocumento = $this->modeloDocumento->folio_documento;
        $this->asuntoDocumento = $this->modeloDocumento->asunto_documento;
        $this->idAreaDestino = $this->modeloDocumento->id_area_destino;
        $this->idAreaDerivar = '';
        $this->observacionesDerivar = '';

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-derivar-documento', 'show');
    }

    public function guardarDerivar()
    {
        $this->validate([
            'idAreaDerivar' => 'required|exists:ta_area,id_area',
            'observacionesDerivar' => 'nullable|max:500'
        ], [
            'idAreaDerivar.required' => 'Debe seleccionar un área de destino',
            'idAreaDerivar.exists' => 'El área seleccionada no existe',
            'observacionesDerivar.max' => 'Las observaciones no pueden exceder 500 caracteres'
        ]);

        $mensajeToastr = null;

        try {
            if (!$this->modeloDocumento) {
                throw new \Exception('No se encontró el documento a derivar');
            }

            // Buscar la transición DERIVAR según el estado actual del documento
            $transicion = Transicion::where('evento_transicion', 'DERIVAR')
                ->where('id_estado_actual_transicion', $this->modeloDocumento->id_estado)
                ->first();

            if (!$transicion) {
                // Si no hay transición definida, derivar usando el servicio directo
                $resultado = $this->documentoService->derivar(
                    $this->modeloDocumento->id_documento,
                    $this->idAreaDerivar,
                    $this->observacionesDerivar
                );
            } else {
                // Usar el servicio de procesarTransicion
                $resultado = $this->documentoService->procesarTransicion(
                    $this->modeloDocumento->id_documento,
                    $transicion->id_transicion,
                    [
                        'id_area_destino' => $this->idAreaDerivar,
                        'observacion' => $this->observacionesDerivar
                    ]
                );
            }

            $this->dispatch('refrescarDocumentos');
            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento derivado correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalDocumento('#modal-derivar-documento', 'hide');
        $this->reset(['idAreaDerivar', 'observacionesDerivar']);

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

    // Método para rectificar (devolver con observaciones)
    #[On('abrirModalRectificarDocumento')]
    public function abrirModalRectificarDocumento($id_documento)
    {
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento);

        $this->numeroDocumento = $this->modeloDocumento->numero_documento;
        $this->folioDocumento = $this->modeloDocumento->folio_documento;
        $this->asuntoDocumento = $this->modeloDocumento->asunto_documento;
        $this->idAreaDestino = $this->modeloDocumento->id_area_destino;
        $this->idAreaDerivar = '';
        $this->observacionesDerivar = '';

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-rectificar-documento', 'show');
    }

    public function guardarRectificar()
    {
        $this->observacionesDerivar = limpiarCadena($this->observacionesDerivar);

        $this->validate([
            'observacionesDerivar' => 'required|max:500'
        ], [
            'observacionesDerivar.required' => 'El motivo de rectificación es obligatorio',
            'observacionesDerivar.max' => 'El motivo de rectificación no puede exceder 500 caracteres'
        ]);

        $mensajeToastr = null;

        try {
            // Validar que el documento esté archivado (estado 6)
            if ($this->modeloDocumento->id_estado != 6) {
                throw new \Exception('Solo se pueden rectificar documentos archivados.');
            }

            // Transición directa a POR RECTIFICAR (estado actual: ARCHIVADO 6 -> siguiente: POR RECTIFICAR 9)
            $transicion = Transicion::where('evento_transicion', 'POR RECTIFICAR')
                ->where('id_estado_actual_transicion', 6)
                ->first();

            if (!$transicion) {
                throw new \Exception('No se encontró la transición a "POR RECTIFICAR" para el estado actual.');
            }

            $this->documentoService->procesarTransicion(
                $this->modeloDocumento->id_documento,
                $transicion->id_transicion,
                [
                    'observacion' => $this->observacionesDerivar
                ]
            );

            $this->dispatch('refrescarDocumentos');
            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento enviado a rectificación', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalDocumento('#modal-rectificar-documento', 'hide');
        $this->reset(['idAreaDerivar', 'observacionesDerivar']);

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

    public function render()
    {
        return view('livewire.documentos.documento.index');
    }
}
