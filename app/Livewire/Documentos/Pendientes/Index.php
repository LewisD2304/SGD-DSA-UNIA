<?php

namespace App\Livewire\Documentos\Pendientes;

use App\Services\Documento\DocumentoService;
use App\Models\Transicion;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public $modeloDocumento = null;
    public $idAreaDerivar = '';
    public $observacionesDerivar = '';
    public $accionActual = '';
    public $tituloModalDerivar = 'Derivar documento';

    protected DocumentoService $documentoService;

    public function __construct()
    {
        $this->documentoService = resolve(DocumentoService::class);
    }

    #[On('abrirModalDetalleDocumento')]
    public function abrirModalDetalleDocumento($id_documento)
    {
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento, ['estado', 'tipoDocumento', 'archivos']);

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-detalle-documento', 'show');
    }

    #[On('abrirModalAccion')]
    public function abrirModalAccion($id_documento, $accion)
    {
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento);
        $this->accionActual = $accion;
        $this->idAreaDerivar = '';
        $this->observacionesDerivar = '';

        // Definir título según la acción
        $titulos = [
            'recepcionar' => 'Recepcionar documento',
            'devolver' => 'Devolver documento',
            'derivar' => 'Derivar documento',
            'subsanar' => 'Subsanar documento'
        ];

        $this->tituloModalDerivar = $titulos[$accion] ?? 'Acción sobre documento';

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-accion-documento', 'show');
    }

    public function ejecutarAccion()
    {
        $reglas = [];

        // Si es derivar o devolver, requiere área de destino
        if (in_array($this->accionActual, ['derivar', 'devolver'])) {
            $reglas['idAreaDerivar'] = 'required|exists:ta_area,id_area';
        }

        // Si es devolver o subsanar, las observaciones son obligatorias
        if (in_array($this->accionActual, ['devolver', 'subsanar'])) {
            $reglas['observacionesDerivar'] = 'required|max:500';
        }

        if (!empty($reglas)) {
            $this->validate($reglas, [
                'idAreaDerivar.required' => 'Debe seleccionar un área de destino',
                'idAreaDerivar.exists' => 'El área seleccionada no existe',
                'observacionesDerivar.required' => 'Las observaciones son obligatorias',
                'observacionesDerivar.max' => 'Las observaciones no pueden exceder 500 caracteres'
            ]);
        }

        $mensajeToastr = null;

        try {
            // Obtener la transición correspondiente según la acción
            $transicion = $this->obtenerTransicion();

            if (!$transicion) {
                throw new \Exception('No se encontró una transición válida para esta acción');
            }

            // Ejecutar la acción según el tipo
            $resultado = $this->documentoService->procesarTransicion(
                $this->modeloDocumento->id_documento,
                $transicion->id_transicion,
                [
                    'id_area_destino' => $this->idAreaDerivar,
                    'observacion' => $this->observacionesDerivar
                ]
            );

            $this->dispatch('refrescarDocumentosPendientes');
            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Acción ejecutada correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalDocumento('#modal-accion-documento', 'hide');
        $this->reset(['idAreaDerivar', 'observacionesDerivar', 'accionActual']);

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

    private function obtenerTransicion()
    {
        // Mapeo de acciones a eventos de transición
        $eventos = [
            'recepcionar' => 'RECEPCIONAR',
            'devolver' => 'DEVOLVER',
            'derivar' => 'DERIVAR',
            'subsanar' => 'SUBSANAR'
        ];

        $evento = $eventos[$this->accionActual] ?? null;

        if (!$evento) {
            return null;
        }

        // Buscar la transición que coincida con el evento y el estado actual del documento
        return Transicion::where('evento_transicion', $evento)
            ->where('id_estado_actual_transicion', $this->modeloDocumento->id_estado)
            ->first();
    }

    public function modalDocumento($nombre, $accion)
    {
        $this->dispatch(
            'modal',
            nombre: $nombre,
            accion: $accion
        );
    }

    public function render()
    {
        return view('livewire.documentos.pendientes.index');
    }
}
