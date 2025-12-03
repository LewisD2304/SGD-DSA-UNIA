<?php

namespace App\Livewire\Seguridad\Usuario;

use App\Enums\EstadoEnum;
use App\Services\Seguridad\PersonaService;
use App\Services\Seguridad\UsuarioService;
use App\Services\Seguridad\RolService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    // Variables para el modal de registro y modificación
    public $tituloModal = 'Registrar nuevo usuario';
    public $modoModal = 1; // 1: Registrar, 2: Modificar, 1: Habilitar, 2: Deshabilitar
    public $modeloUsuario = null;
    public $nombreUsuarioEstado = '';
    public $nombreUsuarioEliminar = '';
    public $idRol;
    public $roles;
    public $idPersona;
    public $claveUsuario;

    #[Validate('required|max:30|min:3', as: 'nombre de usuario')]
    public $nombreUsuario;

    protected UsuarioService $usuarioService;
    protected RolService $rolService;
    protected PersonaService $personaService;

    public function __construct()
    {
        $this->usuarioService = resolve(UsuarioService::class);
        $this->rolService = resolve(RolService::class);
        $this->personaService = resolve(PersonaService::class);
    }


    #[Computed()]
    public function listaRol()
    {
        // Devolver únicamente roles habilitados para que no aparezcan deshabilitados en el select
        return $this->rolService->listarHabilitados();
    }

    #[Computed()]
    public function listaPersona()
    {
        return $this->personaService->listarHabilitados();
    }

    // Guardar un usuario nuevo o modificado
    public function guardarUsuario()
    {
        // Limpiar de cadena
        $this->nombreUsuario = limpiarCadena($this->nombreUsuario, false);
        $this->claveUsuario = limpiarCadena($this->claveUsuario ?? '', false);

        $mensajeToastr = NULL;

        try {

            $this->validate([
                'idPersona' => ['required', 'integer'],
                'idRol' => ['required', 'integer'],
                'nombreUsuario' => [
                    'required',
                    'max:120',
                    'min:3',
                    Rule::unique('ta_usuario', 'nombre_usuario')
                        ->ignore($this->modeloUsuario->id_usuario ?? null, 'id_usuario'),
                ],
                'claveUsuario' => $this->modoModal == 1 ? 'required|min:6' : 'nullable|min:6',
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

        $this->modalUsuario('#modal-usuario', 'hide');
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
        $this->usuarioService->registrar([
            'nombre_usuario' => $this->nombreUsuario,
            'id_rol' => $this->idRol,
            'id_persona' => $this->idPersona,
            'clave_usuario' => bcrypt($this->claveUsuario),
            'estado_usuario' => EstadoEnum::HABILITADO,
        ]);

        $this->dispatch('refrescarUsuarios');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Usuario registrado correctamente', 'top', 'right');

    }


    public function modificar()
    {
        $datos = [
            'nombre_usuario' => $this->nombreUsuario,
            'id_rol' => $this->idRol,
            'id_persona' => $this->idPersona,
        ];

        if (!empty($this->claveUsuario)) {
            $datos['clave_usuario'] = bcrypt($this->claveUsuario);
        }

        $this->usuarioService->modificar($datos, $this->modeloUsuario);

        $this->dispatch('refrescarUsuarios');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Usuario modificado correctamente', 'top', 'right');
    }

    // Abrir modal y cargar datos del modal para registrar o modificar un usuario
    #[On('abrirModalUsuario')]
    public function abrirModalUsuario($id_usuario = null)
    {
        $this->limpiarModal();

        if (!is_null($id_usuario)) {
            $this->tituloModal = 'Modificar usuario';
            $this->modoModal = 2; // Modificar
            $this->modeloUsuario = $this->usuarioService->obtenerPorId($id_usuario);
            $this->nombreUsuario = $this->modeloUsuario->nombre_usuario;
            $this->idRol = $this->modeloUsuario->id_rol;
            $this->idPersona = $this->modeloUsuario->id_persona;
            $this->claveUsuario = '';
        } else {
            $this->tituloModal = 'Registrar nuevo usuario';
            $this->modoModal = 1;
        }

        $this->dispatch('cargando', cargando: 'false');
        $this->modalUsuario('#modal-usuario', 'show');
    }

    // Cambiar el estado de un usuario
    public function cambiarEstadoUsuario()
    {
        $mensajeToastr = NULL;

        try {
            $this->usuarioService->cambiarEstado($this->modeloUsuario, $this->modoModal === 1 ? EstadoEnum::HABILITADO : EstadoEnum::DESHABILITADO);

            $this->dispatch('refrescarUsuarios');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Usuario ' . ($this->modoModal == 1 ? 'habilitado' : 'deshabilitado') . ' correctamente', 'top', 'right');
        } catch (\Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalUsuario('#modal-estado-usuario', 'hide');
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

    #[On('abrirModalEstado')]
    public function abrirModalEstado($id_usuario)
    {
        $this->limpiarModal();

        $this->modeloUsuario = $this->usuarioService->obtenerPorId($id_usuario);
        $this->nombreUsuarioEstado = $this->modeloUsuario->nombreUsuario;
        $this->modoModal = $this->modeloUsuario->estado_usuario === EstadoEnum::HABILITADO ? 2 : 1;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalUsuario('#modal-estado-usuario', 'show');
    }

    // Eliminar un usuario
    public function eliminarUsuario()
    {
        $mensajeToastr = null;

        try {
            $this->usuarioService->eliminar($this->modeloUsuario, ['usuarios']);

            $this->dispatch('refrescarUsuarios');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Usuario eliminado correctamente', 'top', 'right');
        } catch (\Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalUsuario('#modal-eliminar-usuario', 'hide');
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

    // Abrir modal y cargar  datos del modal para eliminar un usuario
    #[On('abrirModalEliminar')]
    public function abrirModalEliminar($id_usuario)
    {
        $this->limpiarModal();
        $this->modeloUsuario = $this->usuarioService->obtenerPorId($id_usuario);
        $this->nombreUsuarioEliminar = $this->modeloUsuario->nombreUsuario;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalUsuario('#modal-eliminar-usuario', 'show');
    }

    // Cerrar modal
    public function modalUsuario($nombre, $accion)
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
        $this->tituloModal = 'Registrar nuevo usuario';
        $this->modoModal = 1;
        $this->reset(['modeloUsuario', 'nombreUsuario', 'nombreUsuarioEliminar', 'nombreUsuarioEstado', 'idRol', 'idPersona', 'claveUsuario']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.seguridad.usuario.index');
    }

}
