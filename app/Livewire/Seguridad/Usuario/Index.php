<?php

namespace App\Livewire\Seguridad\Usuario;

use App\Enums\EstadoEnum;
use App\Services\Seguridad\PersonaService;
use App\Services\Seguridad\UsuarioService;
use App\Services\Seguridad\RolService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    // Variables para el modal
    public $tituloModal = 'Registrar nuevo usuario';
    public $modoModal = 1; // 1: Registrar, 2: Modificar, 1: Habilitar, 2: Deshabilitar
    public $modeloUsuario = null;
    public $nombreUsuarioEstado = '';
    public $nombreUsuarioEliminar = '';

    // Inicializamos las variables para evitar errores de "null"
    public $idRol = '';

    #[Validate('required', as: 'persona')]
    public $idPersona = '';

    #[Validate('required|min:8|max:20', as: 'contraseña')]
    public $claveUsuario = '';

    #[Validate('required|max:30|min:3', as: 'nombre de usuario')]
    public $nombreUsuario = '';

    // Servicios protegidos
    protected UsuarioService $usuarioService;
    protected RolService $rolService;
    protected PersonaService $personaService;

    // CORRECCIÓN: Usamos boot() en lugar de __construct()
    public function boot(
        UsuarioService $usuarioService,
        RolService $rolService,
        PersonaService $personaService
    ) {
        $this->usuarioService = $usuarioService;
        $this->rolService = $rolService;
        $this->personaService = $personaService;
    }

    #[Computed()]
    public function listaRol()
    {
        return $this->rolService->listarHabilitados();
    }

    #[Computed()]
    public function listaPersona()
    {
        return $this->personaService->listarHabilitados();
    }

    public function guardarUsuario()
    {
        // Limpieza básica (asegúrate de tener el helper limpiarCadena o usa trim)
        // $this->nombreUsuario = strtoupper(limpiarCadena($this->nombreUsuario, false));
        $this->nombreUsuario = strtoupper(trim($this->nombreUsuario));

        $mensajeToastr = null;

        try {
            $reglas = [
                'idPersona' => ['required', 'integer'],
                'idRol' => ['required', 'integer'],
                'nombreUsuario' => [
                    'required',
                    'min:3',
                    'max:120',
                    'regex:/^[A-Za-z0-9@._-]+$/',
                    Rule::unique('ta_usuario', 'nombre_usuario')
                        ->ignore($this->modeloUsuario->id_usuario ?? null, 'id_usuario')
                        ->whereNull('au_fechael'),
                ],
                'claveUsuario' => $this->modoModal == 1
                    ? ['required', 'min:8', 'max:20'] // Reglas simplificadas para probar
                    : ['nullable', 'min:8', 'max:20'],
            ];

            $this->validate($reglas);

            if ($this->modoModal == 1) {
                $mensajeToastr = $this->registrar();
            } else {
                $mensajeToastr = $this->modificar();
            }

            // Cerrar modal y limpiar
            $this->modalUsuario('#modal-usuario', 'hide');
            $this->limpiarModal();
            $this->dispatch('cargando');

            if ($mensajeToastr) {
                $this->dispatch('toastr', ...$mensajeToastr);
            }
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            $this->dispatch('erroresValidacion', validacion: $this->getErrorBag()->messages());
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
    }

    #[On('abrirModalEstado')]
    public function abrirModalEstado($id_usuario)
    {
        // NO limpiar el modal aquí, solo cargar los datos necesarios
        $this->modeloUsuario = $this->usuarioService->obtenerPorId($id_usuario);
        $this->nombreUsuarioEstado = $this->modeloUsuario->nombre_usuario;

        // Establecer el modo basado en el estado actual del usuario
        // Si está habilitado, ofrecer deshabilitar (modo 2)
        // Si está deshabilitado, ofrecer habilitar (modo 1)
        $this->modoModal = $this->modeloUsuario->estado_usuario === EstadoEnum::HABILITADO ? 2 : 1;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalUsuario('#modal-estado-usuario', 'show');
    }

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

    #[On('abrirModalEliminar')]
    public function abrirModalEliminar($id_usuario)
    {
        $this->limpiarModal();

        $this->modeloUsuario = $this->usuarioService->obtenerPorId($id_usuario);
        $this->nombreUsuarioEliminar = $this->modeloUsuario->nombre_usuario;

        $this->dispatch('cargando');
        $this->modalUsuario('#modal-eliminar-usuario', 'show');
    }

    public function eliminarUsuario()
    {
        $mensajeToastr = null;

        try {
            $this->usuarioService->eliminar($this->modeloUsuario);
            $this->dispatch('refrescarUsuarios');

            $mensajeToastr = [
                'boton_cerrar' => false,
                'progreso_avance' => true,
                'duracion' => '3000',
                'titulo' => 'Éxito',
                'tipo' => 'success',
                'mensaje' => 'Usuario eliminado correctamente',
                'posicion_y' => 'top',
                'posicion_x' => 'right'
            ];
        } catch (\Exception $e) {
            $mensajeToastr = [
                'boton_cerrar' => false,
                'progreso_avance' => true,
                'duracion' => '5000',
                'titulo' => 'Error',
                'tipo' => 'error',
                'mensaje' => $e->getMessage(),
                'posicion_y' => 'top',
                'posicion_x' => 'right'
            ];
        }

        $this->modalUsuario('#modal-eliminar-usuario', 'hide');
        $this->limpiarModal();
        $this->dispatch('cargando');

        if ($mensajeToastr) {
            $this->dispatch('toastr', ...$mensajeToastr);
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

        return [
            'boton_cerrar' => false,
            'progreso_avance' => true,
            'duracion' => '3000',
            'titulo' => 'Éxito',
            'tipo' => 'success',
            'mensaje' => 'Usuario registrado correctamente',
            'posicion_y' => 'top',
            'posicion_x' => 'right'
        ];
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

        return [
            'boton_cerrar' => false,
            'progreso_avance' => true,
            'duracion' => '3000',
            'titulo' => 'Éxito',
            'tipo' => 'success',
            'mensaje' => 'Usuario modificado correctamente',
            'posicion_y' => 'top',
            'posicion_x' => 'right'
        ];
    }

    #[On('abrirModalUsuario')]
    public function abrirModalUsuario($id_usuario = null)
    {
        if (!is_null($id_usuario)) {
            // Modo edición: limpiar primero
            $this->limpiarModal();

            $this->tituloModal = 'Modificar usuario';
            $this->modoModal = 2;

            $this->modeloUsuario = $this->usuarioService->obtenerPorId($id_usuario, ['persona', 'rol']);
            $this->nombreUsuario = $this->modeloUsuario->nombre_usuario;
            $this->idRol = $this->modeloUsuario->id_rol;
            $this->idPersona = $this->modeloUsuario->id_persona;
            $this->claveUsuario = '';

            // Cargar datos en el frontend (Select2)
            $this->dispatch('cargarDatosModal', [
                'nombreUsuario' => $this->nombreUsuario,
                'idPersona' => $this->idPersona,
                'idRol' => $this->idRol
            ]);
        } else {
            // Modo registro: limpiar todo
            $this->limpiarModal();
            $this->tituloModal = 'Registrar nuevo usuario';
            $this->modoModal = 1;
        }

        $this->dispatch('cargando', cargando: 'false');
        $this->modalUsuario('#modal-usuario', 'show');
    }

    public function modalUsuario($nombre, $accion)
    {
        $this->dispatch('modal', nombre: $nombre, accion: $accion);
    }

    public function limpiarModal()
    {
        $this->tituloModal = 'Registrar nuevo usuario';
        $this->modoModal = 1;
        $this->reset(['modeloUsuario',
        'nombreUsuario',
        'nombreUsuarioEliminar',
        'nombreUsuarioEstado',
        'idRol',
        'idPersona',
        'claveUsuario']);
        $this->resetErrorBag();
        $this->dispatch('limpiarSelect2');
    }

    // Este método a veces es necesario para evitar conflictos con Alpine/Livewire v3
   /* public function toJSON()
    {
        return;
    }*/

    public function render()
    {
        return view('livewire.seguridad.usuario.index');
    }
}
