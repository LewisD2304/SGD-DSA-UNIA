<?php

namespace App\Livewire\Seguridad\Persona;

use App\Enums\EstadoEnum;
use App\Services\Seguridad\PersonaService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    // Variables para el modal de registro y modificación
    public $tituloModal = 'Registrar nueva persona';
    public $modoModal = 1; // 1: Registrar, 2: Modificar, 1: Habilitar, 2: Deshabilitar
    public $modeloPersona = null;
    public $nombrePersonaEstado = '';
    public $nombrePersonaEliminar = '';
    public $soloApellido = false;

    #[Validate('required|max:60|min:3', as: 'números documento')]
    public $numerodocumentoPersona = '';

    #[Validate('required|max:60|min:3', as: 'nombres')]
    public $nombresPersona = '';

    #[Validate('required|max:60|min:3', as: 'apellido paterno')]
    public $apellidoPaternoPersona = '';

    #[Validate('max:60|min:3', as: 'apellido materno')]
    public $apellidoMaternoPersona = '';

    #[Validate('max:20', as: 'celular')]
    public $celularPersona = '';

    #[Validate('email|max:120', as: 'correo')]
    public $correoPersona = '';

    protected PersonaService $personaService;

    public function __construct()
    {
        $this->personaService = resolve(PersonaService::class);
    }

    // Guardar una persona nuevo o modificado
    public function guardarPersona()
    {


        $mensajeToastr = NULL;

        try {

            // Limpiar cadenas
            $this->nombresPersona = limpiarCadena($this->nombresPersona, false);
            $this->apellidoPaternoPersona = limpiarCadena($this->apellidoPaternoPersona, false);
            $this->apellidoMaternoPersona = limpiarCadena($this->apellidoMaternoPersona, false);
            $this->celularPersona = limpiarCadena($this->celularPersona, false);
            $this->correoPersona = limpiarCadena($this->correoPersona, false);

            $this->validate([
                'numerodocumentoPersona' => [
                    'required',
                    'max:20',
                    Rule::unique('ta_persona', 'numerodocumento_persona')
                        ->ignore($this->modeloPersona->id_persona ?? null, 'id_persona'),
                ],
                'nombresPersona' => 'required|max:60|min:3',
                'apellidoPaternoPersona' => 'required|max:60|min:3',
                'apellidoMaternoPersona' => $this->soloApellido ? 'nullable' : 'required|max:60|min:3',
                'celularPersona' => 'max:20',
                'correoPersona' => 'email|max:120',
            ]);

            if ($this->modoModal == 1) {
                $mensajeToastr = $this->registrar();
            } else {
                $mensajeToastr = $this->modificar();
            }
        } catch (\Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalPersona('#modal-persona', 'hide');
        $this->limpiarModal();

        if ($mensajeToastr != NULL) {
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

        $this->personaService->registrar([
            'numerodocumento_persona' => $this->numerodocumentoPersona,
            'nombres_persona' => $this->nombresPersona,
            'apellido_paterno_persona' => $this->apellidoPaternoPersona,
            'apellido_materno_persona' => $this->soloApellido ? null : $this->apellidoMaternoPersona,
            'celular_persona' => $this->celularPersona,
            'correo_persona' => $this->correoPersona,
            'estado_persona' => EstadoEnum::HABILITADO,
        ]);

        $this->dispatch('refrescarPersonas');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Persona registrada correctamente', 'top', 'right');
    }

    public function modificar()
    {
        $this->personaService->modificar([
            'numerodocumento_persona' => $this->numerodocumentoPersona,
            'nombres_persona' => $this->nombresPersona,
            'apellido_paterno_persona' => $this->apellidoPaternoPersona,
            'apellido_materno_persona' => $this->soloApellido ? null : $this->apellidoMaternoPersona,
            'celular_persona' => $this->celularPersona,
            'correo_persona' => $this->correoPersona,
        ], $this->modeloPersona);

        $this->dispatch('refrescarPersonas');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Persona modificada correctamente', 'top', 'right');
    }

    // Abrir modal y cargar datos del modal para registrar o modificar una persona
    #[On('abrirModalPersona')]
    public function abrirModalPersona($id_persona = null)
    {
        $this->limpiarModal();

        if (!is_null($id_persona)) {
            $this->tituloModal = 'Modificar persona';
            $this->modoModal = 2; // Modificar
            $this->modeloPersona = $this->personaService->obtenerPorId($id_persona);
            $this->numerodocumentoPersona = $this->modeloPersona->numerodocumento_persona;
            $this->nombresPersona = $this->modeloPersona->nombres_persona;
            $this->apellidoPaternoPersona = $this->modeloPersona->apellido_paterno_persona;
            $this->apellidoMaternoPersona = $this->modeloPersona->apellido_materno_persona;
            $this->celularPersona = $this->modeloPersona->celular_persona;
            $this->correoPersona = $this->modeloPersona->correo_persona;
            $this->soloApellido = is_null($this->modeloPersona->apellido_materno_persona);
        } else {
            $this->tituloModal = 'Registrar nueva persona';
            $this->modoModal = 1;
        }

        $this->dispatch('cargando', cargando: 'false');
        $this->modalPersona('#modal-persona', 'show');
    }

    // Cambiar el estado de una persona
    public function cambiarEstadoPersona()
    {
        $mensajeToastr = NULL;

        try {
            $this->personaService->cambiarEstado($this->modeloPersona, $this->modoModal === 1 ? EstadoEnum::HABILITADO : EstadoEnum::DESHABILITADO);

            $this->dispatch('refrescarPersonas');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Persona ' . ($this->modoModal == 1 ? 'habilitado' : 'deshabilitado') . ' correctamente', 'top', 'right');
        } catch (\Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalPersona('#modal-estado-persona', 'hide');
        $this->limpiarModal();

        if ($mensajeToastr != NULL) {
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

    // Abrir modal y cargar  datos del modal para cambiar el estado de una persona
    #[On('abrirModalEstado')]
    public function abrirModalEstado($id_persona)
    {
        $this->limpiarModal();

        $this->modeloPersona = $this->personaService->obtenerPorId($id_persona);
        $this->nombrePersonaEstado = $this->modeloPersona->nombrePersona;
        $this->modoModal = $this->modeloPersona->estado_persona === EstadoEnum::HABILITADO ? 2 : 1;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalPersona('#modal-estado-persona', 'show');
    }

    // Eliminar una persona
    public function eliminarPersona()
    {
        $mensajeToastr = null;

        try {
            $this->personaService->eliminar($this->modeloPersona, ['personas']);

            $this->dispatch('refrescarPersonas');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Persona eliminado correctamente', 'top', 'right');
        } catch (\Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalPersona('#modal-eliminar-persona', 'hide');
        $this->limpiarModal();

        if ($mensajeToastr != NULL) {
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

    // Abrir modal y cargar  datos del modal para eliminar una persona
    #[On('abrirModalEliminar')]
    public function abrirModalEliminar($id_persona)
    {
        $this->limpiarModal();
        $this->modeloPersona = $this->personaService->obtenerPorId($id_persona);
        $this->nombrePersonaEliminar = $this->modeloPersona->nombrePersona;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalPersona('#modal-eliminar-persona', 'show');
    }

    // Abrir modal y mostrar detalles de una persona
    #[On('abrirModalDetalle')]
    public function abrirModalDetalle($id_persona)
    {
        $this->limpiarModal();

        $this->modeloPersona = $this->personaService->obtenerPorId($id_persona);

        $this->dispatch('cargando', cargando: 'false');
        $this->modalPersona('#modal-detalle-persona', 'show');
    }

    // Cerrar modal
    public function modalPersona($nombre, $accion)
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
        $this->tituloModal = 'Registrar nueva persona';
        $this->modoModal = 1;
        $this->soloApellido = false;
        $this->reset([
            'modeloPersona',
            'numerodocumentoPersona',
            'nombresPersona',
            'apellidoPaternoPersona',
            'apellidoMaternoPersona',
            'celularPersona',
            'correoPersona',
            'nombrePersonaEliminar',
            'nombrePersonaEstado'
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.seguridad.Persona.index');
    }
}
