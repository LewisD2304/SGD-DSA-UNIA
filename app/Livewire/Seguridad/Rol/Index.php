<?php

namespace App\Livewire\Seguridad\Rol;

use App\Enums\EstadoEnum;
use App\Services\Seguridad\RolService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    // Variables para el modal de registro y modificación
    public $tituloModal = 'Registrar nuevo rol';
    public $modoModal = 1; // 1: Registrar, 2: Modificar, 1: Habilitar, 2: Deshabilitar
    public $modeloRol = null;
    public $nombreRolEstado = '';
    public $nombreRolEliminar = '';

    #[Validate('required|max:30|min:3', as: 'nombre del rol')]
    public $nombreRol;

    protected RolService $rolService;

    public function __construct()
    {
        $this->rolService = resolve(RolService::class);
    }

    // Guardar un rol nuevo o modificado
    public function guardarRol()
    {
        // Limpiar de cadena
        $this->nombreRol = limpiarCadena($this->nombreRol, false);

        $mensajeToastr = NULL;

        try {

            $this->validate([
                'nombreRol' => [
                    'required',
                    'max:30',
                    'min:3',
                    Rule::unique('ta_rol', 'nombre_rol')
                        ->ignore($this->modeloRol->id_rol ?? null, 'id_rol'),
                ],
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

        $this->modalRol('#modal-rol', 'hide');
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

        $this->rolService->registrar([
            'nombre_rol' => $this->nombreRol,
            'estado_rol' => EstadoEnum::HABILITADO,
        ]);

        $this->dispatch('refrescarRoles');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Rol registrado correctamente', 'top', 'right');
    }

    public function modificar()
    {
        $this->rolService->modificar([
            'nombre_rol' => $this->nombreRol,
        ], $this->modeloRol);

        $this->dispatch('refrescarRoles');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Rol modificado correctamente', 'top', 'right');
    }

    // Abrir modal y cargar datos del modal para registrar o modificar un rol
    #[On('abrirModalRol')]
    public function abrirModalRol($id_rol = null)
    {
        $this->limpiarModal();

        if (!is_null($id_rol)) {
            $this->tituloModal = 'Modificar rol';
            $this->modoModal = 2; // Modificar
            $this->modeloRol = $this->rolService->obtenerPorId($id_rol);
            $this->nombreRol = $this->modeloRol->nombre_rol;
        } else {
            $this->tituloModal = 'Registrar nuevo rol';
            $this->modoModal = 1;
        }

        $this->dispatch('cargando', cargando: 'false');
        $this->modalRol('#modal-rol', 'show');
    }

    // Cambiar el estado de un rol
    public function cambiarEstadoRol()
    {
        $mensajeToastr = NULL;

        try {
            $this->rolService->cambiarEstado($this->modeloRol, $this->modoModal === 1 ? EstadoEnum::HABILITADO : EstadoEnum::DESHABILITADO);

            $this->dispatch('refrescarRoles');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Rol ' . ($this->modoModal == 1 ? 'habilitado' : 'deshabilitado') . ' correctamente', 'top', 'right');
        } catch (\Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalRol('#modal-estado-rol', 'hide');
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

    // Abrir modal y cargar  datos del modal para cambiar el estado de un rol
    #[On('abrirModalEstado')]
    public function abrirModalEstado($id_rol)
    {
        $this->limpiarModal();

        $this->modeloRol = $this->rolService->obtenerPorId($id_rol);
        $this->nombreRolEstado = $this->modeloRol->nombreRol;
        $this->modoModal = $this->modeloRol->estado_rol === EstadoEnum::HABILITADO ? 2 : 1;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalRol('#modal-estado-rol', 'show');
    }

    // Eliminar un rol
    public function eliminarRol()
    {
        $mensajeToastr = null;

        try {
            $this->rolService->eliminar($this->modeloRol, ['usuarios']);

            $this->dispatch('refrescarRoles');

            $mensajeToastr = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Rol eliminado correctamente', 'top', 'right');
        } catch (\Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensajeToastr = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalRol('#modal-eliminar-rol', 'hide');
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

    // Abrir modal y cargar  datos del modal para eliminar un rol
    #[On('abrirModalEliminar')]
    public function abrirModalEliminar($id_rol)
    {
        $this->limpiarModal();
        $this->modeloRol = $this->rolService->obtenerPorId($id_rol);
        $this->nombreRolEliminar = $this->modeloRol->nombreRol;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalRol('#modal-eliminar-rol', 'show');
    }

    // Cerrar modal
    public function modalRol($nombre, $accion)
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
        $this->tituloModal = 'Registrar nuevo rol';
        $this->modoModal = 1;
        $this->reset(['modeloRol', 'nombreRol', 'nombreRolEliminar', 'nombreRolEstado']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.seguridad.rol.index');
    }
}
