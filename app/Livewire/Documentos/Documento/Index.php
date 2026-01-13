<?php

namespace App\Livewire\Documentos\Documento;

use App\Enums\EstadoEnum;
use App\Models\ArchivoDocumento;
use App\Models\Estado;
use App\Models\Movimiento;
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
use Illuminate\Support\Facades\Gate;
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
    public $nombreDocumentoAnular = '';

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

    #[Validate('required|exists:ta_catalogo,id_catalogo', as: 'oficina')]
    public $oficina = '';

    #[Validate('max:250', as: 'ruta_documento')]

    public $archivosDocumento = [];
    public $archivosExistentes = [];
    public $rutaActual = 'gestion.documentos.documentos';
    public $areas = [];
    public $tiposDocumento = [];
    public $oficinas = [];

    // Propiedades para derivar documento
    public $idAreaDerivar = '';
    public $observacionesDerivar = '';
    public $observacionSubsanada = false;
    public $archivosEvidenciaRectificacion = [];
    public ?int $documentoArchivarId = null;
    public ?string $documentoArchivarTitulo = null;

    // --- PROPIEDADES OBSERVACIÓN (NUEVO) ---
    public $idAreaObservar = '';
    public $motivoObservacion = '';
    public $archivosEvidenciaObservacion = [];


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

        // Cargar oficinas desde catálogo padre "OFICINAS" (por nombre)
        // Si existe abreviatura para OFICINAS, se puede usar buscarPadre('OF')
        $idPadreOficinas = null;
        try {
            $idPadreOficinas = $this->catalogoService->obtenerIdPorNombre('OFICINAS');
        } catch (\Throwable $e) {
            // fallback: intentar con abreviatura
            $padre = $this->catalogoService->buscarPadre('OF');
            $idPadreOficinas = $padre?->id_catalogo;
        }

        if ($idPadreOficinas) {
            $this->oficinas = $this->catalogoService->listarHijos($idPadreOficinas, [], 0);
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
            $areaUsuario = Auth::user()->persona->id_area ?? null;

            if (!$areaUsuario) {
                throw new \Exception('Su usuario no tiene un Área asignada, por lo tanto no puede registrar documentos.');
            }
            $this->idAreaRemitente = $areaUsuario;
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
                'oficina' => 'required|exists:ta_catalogo,id_catalogo',
                'observacionDocumento' => 'nullable|max:500',
            ];

            // Validar archivos (múltiples) solo si se está creando o modificando con nuevos archivos
            if ($this->modoModal == 1 || !empty($this->archivosDocumento)) {
                $reglas['archivosDocumento'] = 'nullable|array|max:10';
                $reglas['archivosDocumento.*'] = 'file|mimetypes:application/pdf,image/png,image/jpeg|max:10240';
            }

            $mensajes = [
                'folioDocumento.required' => 'El campo folio es obligatorio cuando se adjuntan archivos.',
                'folioDocumento.numeric' => 'El campo folio debe ser un número.',
                'folioDocumento.min' => 'El campo folio debe ser al menos 1.',
                'folioDocumento.max' => 'El campo folio no puede ser mayor a 999999.',
                'archivosDocumento.array' => 'Los archivos deben ser un conjunto válido.',
                'archivosDocumento.max' => 'No puedes subir más de 10 archivos.',
                'archivosDocumento.*.file' => 'Cada archivo debe ser un archivo válido.',
                'archivosDocumento.*.mimetypes' => 'Solo se permiten archivos PDF, PNG o JPEG.',
                'archivosDocumento.*.max' => 'Cada archivo no debe exceder 10MB.',
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
            'oficina_catalogo' => $this->oficina,
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
            'oficina_catalogo' => $this->oficina,
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
            // Incluir adjuntos del último DERIVAR cuando el documento ya fue recepcionado
            $docBase = $this->documentoService->obtenerPorId($id_documento, ['estado']);
            $estadoNombre = strtoupper(optional($docBase?->estado)->nombre_estado);
            $yaRecepcionado = ($docBase?->fecha_recepcion_documento !== null)
                || in_array($estadoNombre, ['RECEPCIONADO', 'EN TRÁMITE', 'EN TRAMITE']);

            $this->modeloDocumento = $this->documentoService->obtenerPorIdParaArea(
                $id_documento,
                $this->idAreaRemitente,
                ['archivos'],
                $yaRecepcionado
            );
            $this->numeroDocumento = $this->modeloDocumento->numero_documento;
            $this->folioDocumento = $this->modeloDocumento->folio_documento;
            $this->tipoDocumentoCatalogo = $this->modeloDocumento->tipo_documento_catalogo;
            $this->asuntoDocumento = $this->modeloDocumento->asunto_documento;
            $this->observacionDocumento = $this->modeloDocumento->observacion_documento;
            $this->idAreaDestino = $this->modeloDocumento->id_area_destino;
            $this->oficina = $this->modeloDocumento->id_area;
            $this->oficina = $this->modeloDocumento->oficina_catalogo ?? '';
            $this->archivosExistentes = $this->modeloDocumento->archivos ?? collect();
        } else {
            $this->tituloModal = 'Registrar nuevo documento';
            $this->modoModal = 1;

            //SECRE a DIRECCION DSA
            $usuario = Auth::user();
            $nombreAreaUsuario = strtoupper($usuario->persona->area->nombre_area ?? '');

            // Verificamos si el área del usuario contiene "MESA DE PARTES"
            if (str_contains($nombreAreaUsuario, 'MESA DE PARTES')) {
                // Buscamos el ID del área "DIRECCION DSA"
                foreach ($this->areas as $area) {
                    if (str_contains(strtoupper($area->nombre_area), 'DIRECCION DSA')) {
                        // 1. Asignamos el valor a la propiedad de Livewire
                        $this->idAreaDestino = $area->id_area;

                        // 2. Enviamos evento al navegador para actualizar visualmente el Select2
                        $this->dispatch('preseleccionar_area_destino', valor: $area->id_area);
                        break;
                    }
                }
            }
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

    #[On('abrirModalAnularDocumento')]
    public function abrirModalAnularDocumento($id_documento)
    {
        $mensajeToastr = null;

        try {
            $this->limpiarModal();

            // Verificar permiso
            if (!Gate::allows('autorizacion', ['ANULAR', 'DOCUMENTOS'])) {
                throw new \Exception('No tiene permisos para anular documentos');
            }

            $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento, ['estado']);
            $estadoDocumento = strtoupper(optional($this->modeloDocumento?->estado)->nombre_estado);

            // Validar que no esté en estados protegidos
            $estadosProtegidos = ['ARCHIVADO', 'RECEPCIONADO', 'EN TRÁMITE', 'EN TRAMITE'];
            if (in_array($estadoDocumento, $estadosProtegidos)) {
                throw new \Exception("No se puede anular un documento en estado {$estadoDocumento}");
            }

            $this->nombreDocumentoAnular = $this->modeloDocumento->numero_documento;

            $this->dispatch('cargando', cargando: 'false');
            $this->modalDocumento('#modal-anular-documento', 'show');

        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
            $this->dispatch('cargando', cargando: 'false');
        }

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

    public function anularDocumento()
    {
        $mensajeToastr = null;

        try {
            // Verificar permiso nuevamente
            if (!Gate::allows('autorizacion', ['ANULAR', 'DOCUMENTOS'])) {
                throw new \Exception('No tiene permisos para anular documentos');
            }

            // Obtener el estado ANULADO (id 7)
            $estadoAnulado = Estado::where('nombre_estado', 'ANULADO')->first();
            if (!$estadoAnulado) {
                throw new \Exception('No se encontró el estado ANULADO en el sistema');
            }

            // Validar estado actual
            $this->modeloDocumento = $this->documentoService->obtenerPorId($this->modeloDocumento->id_documento, ['estado']);
            $estadoDocumento = strtoupper(optional($this->modeloDocumento?->estado)->nombre_estado);

            $estadosProtegidos = ['ARCHIVADO', 'RECEPCIONADO', 'EN TRÁMITE', 'EN TRAMITE'];
            if (in_array($estadoDocumento, $estadosProtegidos)) {
                throw new \Exception("No se puede anular un documento en estado {$estadoDocumento}");
            }

            // Actualizar estado del documento
            $this->modeloDocumento->id_estado = $estadoAnulado->id_estado;
            $this->modeloDocumento->au_fechamd = now();
            $this->modeloDocumento->au_usuariomd = Auth::user()->id_usuario;
            $this->modeloDocumento->save();

            // Registrar en el historial (ta_movimiento)
            $movimiento = new Movimiento();
            $movimiento->id_documento = $this->modeloDocumento->id_documento;
            $movimiento->id_estado = $estadoAnulado->id_estado;
            $movimiento->id_area_origen = $this->modeloDocumento->id_area_origen;
            $movimiento->id_area_destino = $this->modeloDocumento->id_area_origen; // Mismo origen y destino
            $movimiento->observacion_doc_movimiento = 'Documento anulado por el usuario';
            $movimiento->tipo_cargo_catalogo = null;
            $movimiento->fecha_recepcion = now();
            $movimiento->au_fechacr = now();
            $movimiento->au_usuariocr = Auth::user()->id_usuario;
            $movimiento->save();

            $this->dispatch('refrescarDocumentos');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento anulado correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalDocumento('#modal-anular-documento', 'hide');
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

    #[On('abrirModalDetalleDocumento')]
    public function abrirModalDetalleDocumento($id_documento)
    {
        $this->limpiarModal();

        // Determinar si se deben incluir archivos de derivaciones
        // Regla: cuando el documento ya fue recepcionado (por Mesa u otra área),
        // el área destino debe visualizar los archivos adjuntos del último DERIVAR.
        $docEstado = $this->documentoService->obtenerPorId($id_documento, ['estado']);
        $estadoNombre = strtoupper(optional($docEstado?->estado)->nombre_estado);
        $yaRecepcionado = ($docEstado?->fecha_recepcion_documento !== null)
            || in_array($estadoNombre, ['RECEPCIONADO', 'EN TRÁMITE', 'EN TRAMITE']);

        $this->modeloDocumento = $this->documentoService->obtenerPorIdParaArea(
            $id_documento,
            $this->idAreaRemitente,
            ['estado', 'tipoDocumento', 'archivos'],
            $yaRecepcionado // Incluir derivaciones si ya fue recepcionado
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
            'oficina',
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
            $archivo = ArchivoDocumento::find($idArchivo);
            if ($archivo) {
                // Eliminar archivo físico
                $this->archivoService->eliminarArchivo($archivo->ruta_archivo);
                // Eliminar registro de BD
                $archivo->delete;

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
        $this->observacionSubsanada = false;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-derivar-documento', 'show');
    }

    public function guardarDerivar()
    {
        $reglas = [
            'idAreaDerivar' => 'required|exists:ta_area,id_area',
            'observacionesDerivar' => 'nullable|max:500'
        ];

        $mensajes = [
            'idAreaDerivar.required' => 'Debe seleccionar un área de destino',
            'idAreaDerivar.exists' => 'El área seleccionada no existe',
            'observacionesDerivar.max' => 'Las observaciones no pueden exceder 500 caracteres'
        ];

        // Si el estado es OBSERVACION RECEPCIONADO, validar que el checkbox esté marcado
        $nombreEstado = strtoupper($this->modeloDocumento?->estado?->nombre_estado ?? '');
        if (str_contains($nombreEstado, 'OBSERVACION RECEPCIONADO')) {
            $reglas['observacionSubsanada'] = 'accepted';
            $mensajes['observacionSubsanada.accepted'] = 'Debe confirmar que la observación ha sido subsanada';
        }

        $this->validate($reglas, $mensajes);

        $mensajeToastr = null;

        try {
            if (!$this->modeloDocumento) {
                throw new \Exception('No se encontró el documento a derivar');
            }

            // Si el checkbox de subsanación está marcado, usar la transición SUBSANADO en lugar de DERIVAR
            $eventoTransicion = 'DERIVAR';
            if ($this->observacionSubsanada) {
                $eventoTransicion = 'SUBSANADO';
            }

            // Buscar la transición según el estado actual del documento
            $transicion = Transicion::where('evento_transicion', $eventoTransicion)
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
        $this->reset(['idAreaDerivar', 'observacionesDerivar', 'observacionSubsanada']);

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
        $this->archivosEvidenciaRectificacion = [];

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-rectificar-documento', 'show');
    }

    public function guardarRectificar()
    {
        $this->observacionesDerivar = limpiarCadena($this->observacionesDerivar);

        $reglas = [
            'observacionesDerivar' => 'required|max:500',
        ];

        // Validaciones de archivos solo si se adjuntaron archivos
        if (!empty($this->archivosEvidenciaRectificacion)) {
            $reglas['archivosEvidenciaRectificacion'] = 'nullable|array|max:10';
            $reglas['archivosEvidenciaRectificacion.*'] = 'file|mimetypes:application/pdf,image/png,image/jpeg|max:10240';
        }

        $mensajesCustim = [
            'observacionesDerivar.required' => 'El motivo de rectificación es obligatorio',
            'observacionesDerivar.max' => 'El motivo de rectificación no puede exceder 500 caracteres',
            'archivosEvidenciaRectificacion.array' => 'Los archivos deben ser un conjunto válido.',
            'archivosEvidenciaRectificacion.max' => 'No puedes subir más de 10 archivos de evidencia.',
            'archivosEvidenciaRectificacion.*.file' => 'Cada archivo de evidencia debe ser un archivo válido.',
            'archivosEvidenciaRectificacion.*.mimetypes' => 'Solo se permiten archivos PDF, PNG o JPEG.',
            'archivosEvidenciaRectificacion.*.max' => 'Cada archivo de evidencia no debe exceder 10MB.',
        ];

        $this->validate($reglas, $mensajesCustim);

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

            // Guardar archivos de evidencia (si se adjuntaron)
            if (!empty($this->archivosEvidenciaRectificacion)) {
                $usuario = \Illuminate\Support\Facades\Auth::user();
                $idAreaUsuario = $usuario->persona->id_area ?? null;
                $archivosInfo = $this->archivoService->guardarMultiplesArchivos(
                    archivos: $this->archivosEvidenciaRectificacion,
                    ruta: 'gestion/documentos/evidencias_rectificacion',
                    idDocumento: $this->modeloDocumento->id_documento,
                    idArea: $idAreaUsuario
                );

                foreach ($archivosInfo as $info) {
                    \App\Models\ArchivoDocumento::create(array_merge($info, [
                        'tipo_archivo' => 'evidencia_rectificacion'
                    ]));
                }
            }

            $this->dispatch('refrescarDocumentos');
            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Documento enviado a rectificación', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalDocumento('#modal-rectificar-documento', 'hide');
        $this->reset(['idAreaDerivar', 'observacionesDerivar', 'archivosEvidenciaRectificacion']);

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

    // Quitar un archivo del listado antes de guardar
    public function quitarArchivoEvidencia(int $index): void
    {
        if (isset($this->archivosEvidenciaRectificacion[$index])) {
            unset($this->archivosEvidenciaRectificacion[$index]);
            $this->archivosEvidenciaRectificacion = array_values($this->archivosEvidenciaRectificacion);
        }
    }

    public function abrirModalArchivar(int $id_documento): void
    {
        $documento = $this->documentoService->obtenerPorId($id_documento);

        if (!$documento) {
            $this->dispatch(
                'toastr',
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

            $transicion = Transicion::whereIn('evento_transicion', ['ARCHIVADO', 'ARCHIVAR'])
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

    // --- OBSERVACIÓN (NUEVA FUNCIONALIDAD) ---

    #[On('abrirModalObservarDocumento')]
    public function abrirModalObservarDocumento($id_documento)
    {
        $this->limpiarModal();

        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento, ['areaRemitente', 'areaDestino', 'estado']);

        if (!$this->modeloDocumento) return;

        // Cargar datos para mostrar
        $this->numeroDocumento = $this->modeloDocumento->numero_documento;
        $this->folioDocumento = $this->modeloDocumento->folio_documento;
        $this->asuntoDocumento = $this->modeloDocumento->asunto_documento;

        // Configurar valores por defecto para observar
        $this->idAreaObservar = $this->modeloDocumento->id_area_remitente; // Sugerir remitente original
        $this->motivoObservacion = '';
        $this->archivosEvidenciaObservacion = [];

        $this->dispatch('cargando', cargando: 'false');
        $this->dispatch('inicializarSelect2Observacion');
        $this->modalDocumento('#modal-observacion-documento', 'show');
    }

    public function guardarObservacion()
    {
        $this->motivoObservacion = limpiarCadena($this->motivoObservacion, false);

        $reglas = [
            'idAreaObservar' => 'required|exists:ta_area,id_area',
            'motivoObservacion' => 'required|max:500|min:5',
        ];

        // Validaciones de archivos solo si se adjuntaron archivos
        if (!empty($this->archivosEvidenciaObservacion)) {
            $reglas['archivosEvidenciaObservacion'] = 'nullable|array|max:10';
            $reglas['archivosEvidenciaObservacion.*'] = 'file|mimetypes:application/pdf,image/png,image/jpeg|max:10240';
        }

        $mensajesCustim = [
            'idAreaObservar.required' => 'Debe seleccionar el área destino.',
            'motivoObservacion.required' => 'El motivo de la observación es obligatorio.',
            'motivoObservacion.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivoObservacion.max' => 'El motivo no puede exceder 500 caracteres.',
            'archivosEvidenciaObservacion.array' => 'Los archivos deben ser un conjunto válido.',
            'archivosEvidenciaObservacion.max' => 'No puedes subir más de 10 archivos de evidencia.',
            'archivosEvidenciaObservacion.*.file' => 'Cada archivo de evidencia debe ser un archivo válido.',
            'archivosEvidenciaObservacion.*.mimetypes' => 'Solo se permiten archivos PDF, PNG o JPEG.',
            'archivosEvidenciaObservacion.*.max' => 'Cada archivo de evidencia no debe exceder 10MB.',
        ];

        $this->validate($reglas, $mensajesCustim);

        $mensajeToastr = null;

        try {
            $this->documentoService->observar(
                $this->modeloDocumento->id_documento,
                (int) $this->idAreaObservar,
                $this->motivoObservacion,
                $this->archivosEvidenciaObservacion
            );

            $this->dispatch('refrescarDocumentos');
            $mensajeToastr = mensajeToastr(false, true, '3000', 'Observado', 'warning', 'Documento observado correctamente.', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalDocumento('#modal-observacion-documento', 'hide');
        $this->reset(['idAreaObservar', 'motivoObservacion', 'archivosEvidenciaObservacion']);

        if ($mensajeToastr !== null) {
            $this->dispatch('toastr', ...$mensajeToastr);
        }
    }

    public function quitarArchivoObservacion($index)
    {
        if (isset($this->archivosEvidenciaObservacion[$index])) {
            array_splice($this->archivosEvidenciaObservacion, $index, 1);
        }
    }

    public function limpiarFechas()
    {
        $this->reset(['fechaInicio', 'fechaFin']);
    }

    public function render()
    {
        return view('livewire.documentos.documento.index');
    }
}
