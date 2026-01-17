<?php

namespace App\Livewire\Seguridad\Area;

use App\Enums\EstadoEnum;
use App\Services\Configuracion\AreaService;
use App\Services\Seguridad\PersonaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Variables para el modal de registro y modificación
    public $tituloModal = 'Registrar nuevo área';
    public $modoModal = 1; // 0: Registrar, 1: Modificar
    public $modeloArea = null;
    public $nombreAreaEstado = '';
    public $nombreAreaEliminar = '';
    public $areaDetalle = null;
    public $validacionEliminar = '';
    public $buscar = '';
    public $permisos = [];
    public $personasSeleccionadas = [];
    public $personasDisponibles = [];

    #[Validate('required|max:100|min:3', as: 'nombre del área')]
    public $nombreArea = '';

    #[Validate('required|max:10|min:2', as: 'siglas')]
    public $siglasArea = '';

    protected AreaService $areaService;
    protected PersonaService $personaService;

    public function __construct()
    {
        $this->areaService = resolve(AreaService::class);
        $this->personaService = resolve(PersonaService::class);
    }

    // Obtener áreas con paginación
    public function getAreasProperty()
    {
        return $this->areaService->listarPaginado(
            paginado: 10,
            buscar: $this->buscar ?: null,
            columnaOrden: 'id_area',
            orden: 'asc',
            relaciones: ['personas']
        );
    }

    // Obtener permisos disponibles
    private function obtenerPermisos()
    {
        $permisosDisponibles = ['REGISTRAR', 'MODIFICAR', 'CAMBIAR_ESTADO', 'ELIMINAR'];
        $this->permisos = [];

        foreach ($permisosDisponibles as $permiso) {
            if (Auth::check() && Auth::user()->can('autorizacion', [$permiso, 'AREA'])) {
                $this->permisos[$permiso] = true;
            }
        }
    }

    // Guardar un área nuevo o modificado
    public function guardarArea()
    {
        $mensajeToastr = null;

        try {
            // Limpiar cadenas
            $this->nombreArea = limpiarCadena($this->nombreArea, false);
            $this->siglasArea = limpiarCadena($this->siglasArea, false);

            $this->validate([
                'nombreArea' => [
                    'required',
                    'max:100',
                    'min:3',
                    Rule::unique('ta_area', 'nombre_area')
                        ->ignore($this->modeloArea->id_area ?? null, 'id_area'),
                ],
                'siglasArea' => 'required|max:10|min:2',
            ]);

            if ($this->modoModal == 0) {
                $mensajeToastr = $this->registrar();
            } else {
                $mensajeToastr = $this->modificar();
            }
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalArea('#modal-area', 'hide');
        $this->limpiarModal();

        if ($mensajeToastr != null) {
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
        $area = $this->areaService->registrar([
            'nombre_area' => $this->nombreArea,
            'siglas_area' => $this->siglasArea,
            'estado_area' => EstadoEnum::HABILITADO,
        ]);

        // Asignar personas al área
        if (!empty($this->personasSeleccionadas)) {
            $this->areaService->asignarPersonas($area, $this->personasSeleccionadas);
        }

        $this->dispatch('refrescarAreas');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Área registrada correctamente', 'top', 'right');
    }

    public function modificar()
    {
        $this->areaService->modificar([
            'nombre_area' => $this->nombreArea,
            'siglas_area' => $this->siglasArea,
        ], $this->modeloArea);

        // Sincronizar personas asignadas al área
        $this->areaService->asignarPersonas($this->modeloArea, $this->personasSeleccionadas);

        $this->dispatch('refrescarAreas');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Área modificada correctamente', 'top', 'right');
    }

    // Abrir modal y cargar datos del modal para registrar o modificar un área
    #[On('abrirModalArea')]
    public function abrirModalArea($id_area = null)
    {
        $this->limpiarModal();

        // Cargar personas habilitadas disponibles
        $this->personasDisponibles = $this->personaService->listarHabilitados();

        if (!is_null($id_area)) {
            $this->tituloModal = 'Modificar área';
            $this->modoModal = 1; // Modificar
            $this->modeloArea = $this->areaService->obtenerPorId($id_area, ['personas']);
            $this->nombreArea = $this->modeloArea->nombre_area;
            $this->siglasArea = $this->modeloArea->siglas_area;

            // Cargar personas asignadas al área
            $this->personasSeleccionadas = $this->modeloArea->personas->pluck('id_persona')->toArray();
        } else {
            $this->tituloModal = 'Registrar nuevo área';
            $this->modoModal = 0;
            $this->personasSeleccionadas = [];
        }

        $this->dispatch('cargando', cargando: 'false');
        $this->modalArea('#modal-area', 'show');
    }

    // Cambiar el estado de un área
    public function cambiarEstadoArea()
    {
        $mensajeToastr = null;

        try {
            $this->areaService->cambiarEstado($this->modeloArea, $this->modoModal === 1 ? EstadoEnum::HABILITADO : EstadoEnum::DESHABILITADO);

            $this->dispatch('refrescarAreas');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Área ' . ($this->modoModal == 1 ? 'habilitada' : 'deshabilitada') . ' correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalArea('#modal-estado-area', 'hide');
        $this->limpiarModal();

        if ($mensajeToastr != null) {
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

    // Abrir modal y cargar datos del modal para cambiar el estado de un área
    #[On('abrirModalEstado')]
    public function abrirModalEstado($id_area)
    {
        $this->limpiarModal();

        $this->modeloArea = $this->areaService->obtenerPorId($id_area);
        $this->nombreAreaEstado = $this->modeloArea->nombre_area;
        $this->modoModal = $this->modeloArea->estado_area === EstadoEnum::HABILITADO ? 2 : 1;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalArea('#modal-estado-area', 'show');
    }

    // Eliminar un área
    public function eliminarArea()
    {
        $mensajeToastr = null;

        try {
            $this->areaService->eliminar($this->modeloArea, ['personas', 'documentos']);

            $this->dispatch('refrescarAreas');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Área eliminada correctamente', 'top', 'right');
        } catch (\Exception $e) {
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalArea('#modal-eliminar-area', 'hide');
        $this->limpiarModal();

        if ($mensajeToastr != null) {
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

    // Abrir modal y cargar datos del modal para eliminar un área
    #[On('abrirModalEliminar')]
    public function abrirModalEliminar($id_area)
    {
        $this->limpiarModal();
        $this->modeloArea = $this->areaService->obtenerPorId($id_area);
        $this->nombreAreaEliminar = $this->modeloArea->nombre_area;

        // Validar relaciones antes de permitir eliminar
        if ($this->modeloArea->personas()->count() > 0) {
            $this->validacionEliminar = 'No se puede eliminar un área que tiene personas asignadas.';
        } elseif ($this->modeloArea->documentos()->count() > 0) {
            $this->validacionEliminar = 'No se puede eliminar un área que tiene documentos asignados.';
        }

        $this->dispatch('cargando', cargando: 'false');
        $this->modalArea('#modal-eliminar-area', 'show');
    }

    // Abrir modal y mostrar detalles de un área
    #[On('abrirModalDetalle')]
    public function abrirModalDetalle($id_area)
    {
        $this->limpiarModal();

        $this->areaDetalle = $this->areaService->obtenerPorId($id_area);

        $this->dispatch('cargando', cargando: 'false');
        $this->modalArea('#modal-detalle-area', 'show');
    }

    // Listener para refrescar áreas
    #[On('refrescarAreas')]
    public function refrescarAreas()
    {
        $this->resetPage();
    }

    // Cerrar modal
    public function modalArea($nombre, $accion)
    {
        $this->dispatch(
            'modal',
            nombre: $nombre,
            accion: $accion
        );
    }

    // Limpiar modal
    public function limpiarModal()
    {
        $this->tituloModal = 'Registrar nuevo área';
        $this->modoModal = 0;
        $this->validacionEliminar = '';
        $this->personasSeleccionadas = [];
        $this->personasDisponibles = [];
        $this->reset([
            'modeloArea',
            'areaDetalle',
            'nombreArea',
            'siglasArea',
            'nombreAreaEliminar',
            'nombreAreaEstado'
        ]);
        $this->resetErrorBag();
    }

    public function mount()
    {
        $this->obtenerPermisos();
    }

    public function render()
    {
        return view('livewire.seguridad.Area.index', [
            'areas' => $this->areas,
            'permisos' => $this->permisos,
        ]);
    }
}
