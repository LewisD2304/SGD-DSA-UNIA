<?php

namespace App\Livewire\Seguridad\Menu;

use App\Enums\EstadoEnum;
use App\Models\Catalogo;
use App\Services\Seguridad\MenuService;
use App\Enums\CatalogoEnum;
use App\Services\Configuracion\Catalogo\CatalogoService;
use Exception;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Lista de menús | GESTIÓN DOCUMENTAL')]
class Index extends Component
{
    // Variables para el modal de registro, modificación, eliminación y cambio de estado
    public $titulo_modal = 'Registrar nuevo menú';
    public $modo_modal = 1; // 1: Registrar, 2: Modificar, 1: Habilitar, 2: Deshabilitar
    public $modelo_menu = null;
    public $nombre_menu_estado = '';
    public $nombre_menu_eliminar = '';
    #[Validate('required|max:60|min:3', as: 'nombre del menú')]
    public $nombre_menu;
    #[Validate('required', as: 'ruta del menú')]
    public $ruta_menu;
    #[Validate('required', as: 'acciones')]
    public $acciones = [];

    protected MenuService $menu_service;
    protected CatalogoService $catalogo_service;

    public function __construct()
    {
        $this->menu_service = resolve(MenuService::class);
        $this->catalogo_service = resolve(CatalogoService::class);
    }

    // Guardar un menú nuevo o modificado
    public function guardarMenu()
    {
        // Limpiar de cadena
        $this->nombre_menu = limpiarCadena($this->nombre_menu, false, false);
        $this->ruta_menu = limpiarCadena($this->ruta_menu, false, false);

        $mensaje_t = NULL;

        try {

            $this->validate([
                'nombre_menu' => [
                    'required',
                    'max:60',
                    'min:3',
                    Rule::unique('ta_menu', 'nombre_menu')
                        ->ignore($this->modelo_menu->id_menu ?? null, 'id_menu'),
                ],
                'ruta_menu' => 'required',
                'acciones' => 'required',
            ]);

            if ($this->modo_modal == 1) {
                $mensaje_t = $this->registrar();
            } else {
                $mensaje_t = $this->modificar();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Emitir un evento para mostrar el Toastr con los mensajes de error
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            $this->dispatch('errores_validacion', validacion: $this->getErrorBag()->messages());
            return;
        } catch (Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensaje_t = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');

            if ($mensaje_t != NULL) {
                $this->dispatch(
                    'toastr',
                    boton_cerrar: $mensaje_t['boton_cerrar'],
                    progreso_avance: $mensaje_t['progreso_avance'],
                    duracion: $mensaje_t['duracion'],
                    titulo: $mensaje_t['titulo'],
                    tipo: $mensaje_t['tipo'],
                    mensaje: $mensaje_t['mensaje'],
                    posicion_y: $mensaje_t['posicion_y'],
                    posicion_x: $mensaje_t['posicion_x']
                );
            }
            return;
        }

        $this->modalMenu('#modal-menu', 'hide');
        $this->limpiarModal();

        if ($mensaje_t != NULL) {
            $this->dispatch(
                'toastr',
                boton_cerrar: $mensaje_t['boton_cerrar'],
                progreso_avance: $mensaje_t['progreso_avance'],
                duracion: $mensaje_t['duracion'],
                titulo: $mensaje_t['titulo'],
                tipo: $mensaje_t['tipo'],
                mensaje: $mensaje_t['mensaje'],
                posicion_y: $mensaje_t['posicion_y'],
                posicion_x: $mensaje_t['posicion_x']
            );
        }
    }

    public function registrar()
    {
        $this->menu_service->registrar([
            'nombre_menu' => $this->nombre_menu,
            'ruta_menu' => $this->ruta_menu,
            'estado_menu' => EstadoEnum::HABILITADO,
        ], $this->acciones);

        $this->dispatch('refrescar_menus');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Menú registrado correctamente', 'top', 'right');
    }

    public function modificar()
    {
        $this->menu_service->modificar([
            'nombre_menu' => $this->nombre_menu,
            'ruta_menu' => $this->ruta_menu,
        ], $this->modelo_menu, $this->acciones);

        $this->dispatch('refrescar_menus');

        return mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Menú modificado correctamente', 'top', 'right');
    }

    // Abrir modal y cargar datos del modal para registrar o modificar un menú
    #[On('abrirModalMenu')]
    public function abrirModalMenu($id_menu = null)
    {
        $this->limpiarModal();

        if (!is_null($id_menu)) {
            $this->titulo_modal = 'Modificar menú';
            $this->modo_modal = 2; // Modificar
            $this->modelo_menu = $this->menu_service->obtenerPorId(['acciones.tipoAccion'], $id_menu);
            $this->nombre_menu = $this->modelo_menu->nombre_menu;
            $this->ruta_menu = $this->modelo_menu->ruta_menu;
            $this->acciones = $this->modelo_menu->acciones->pluck('tipoAccion.id_catalogo')->toArray() ?? [];
            $this->dispatch('autocompletado');

        } else {
            $this->titulo_modal = 'Registrar nuevo menú';
            $this->modo_modal = 1;
        }

        $this->dispatch('cargando', cargando: 'false');
        $this->modalMenu('#modal-menu', 'show');
    }

    // Cambiar el estado de un menú
    public function cambiarEstadoMenu()
    {
        $mensaje_t = NULL;

        try {
            $this->menu_service->cambiarEstado($this->modelo_menu, $this->modo_modal === 1 ? EstadoEnum::HABILITADO : EstadoEnum::DESHABILITADO);

            $this->dispatch('refrescar_menus');

            $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Menú ' . ($this->modo_modal == 1 ? 'habilitado' : 'deshabilitado') . ' correctamente', 'top', 'right');
        } catch (Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensaje_t = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalMenu('#modal-estado-menu', 'hide');
        $this->limpiarModal();

        if ($mensaje_t != NULL) {
            $this->dispatch(
                'toastr',
                boton_cerrar: $mensaje_t['boton_cerrar'],
                progreso_avance: $mensaje_t['progreso_avance'],
                duracion: $mensaje_t['duracion'],
                titulo: $mensaje_t['titulo'],
                tipo: $mensaje_t['tipo'],
                mensaje: $mensaje_t['mensaje'],
                posicion_y: $mensaje_t['posicion_y'],
                posicion_x: $mensaje_t['posicion_x']
            );
        }
    }

    // Abrir modal y cargar  datos del modal para cambiar el estado de un menú
    #[On('abrirModalEstado')]
    public function abrirModalEstado($id_menu)
    {
        $this->limpiarModal();

        $this->modelo_menu = $this->menu_service->obtenerPorId([], $id_menu);
        $this->nombre_menu_estado = $this->modelo_menu->nombre_menu;
        $this->modo_modal = $this->modelo_menu->estado_menu === EstadoEnum::HABILITADO ? 2 : 1;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalMenu('#modal-estado-menu', 'show');
    }

    // Eliminar un menú
    public function eliminarMenu()
    {
        $mensaje_t = null;

        try {
            $this->menu_service->eliminar($this->modelo_menu, ['acciones.permisos']); // ['acciones', 'tipoAcciones]

            $this->dispatch('refrescar_menus');

            $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Menú eliminado correctamente', 'top', 'right');
        } catch (Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensaje_t = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalMenu('#modal-eliminar-menu', 'hide');
        $this->limpiarModal();

        if ($mensaje_t != NULL) {
            $this->dispatch(
                'toastr',
                boton_cerrar: $mensaje_t['boton_cerrar'],
                progreso_avance: $mensaje_t['progreso_avance'],
                duracion: $mensaje_t['duracion'],
                titulo: $mensaje_t['titulo'],
                tipo: $mensaje_t['tipo'],
                mensaje: $mensaje_t['mensaje'],
                posicion_y: $mensaje_t['posicion_y'],
                posicion_x: $mensaje_t['posicion_x']
            );
        }
    }

    // Abrir modal y cargar  datos del modal para eliminar un módulo
    #[On('abrirModalEliminar')]
    public function abrirModalEliminar($id_menu)
    {
        $this->limpiarModal();
        $this->modelo_menu = $this->menu_service->obtenerPorId([], $id_menu);
        $this->nombre_menu_eliminar = $this->modelo_menu->nombre_menu;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalMenu('#modal-eliminar-menu', 'show');
    }

    // Abrir o cerrar modal
    public function modalMenu($nombre, $accion)
    {
        $this->dispatch(
            'modal',
            nombre: $nombre,
            accion: $accion
        );
    }

    // Limpiar modales de registro, modificación, eliminación y cambio de estado
    public function limpiarModal()
    {
        $this->titulo_modal = 'Registrar nuevo menú';
        $this->modo_modal = 1;
        $this->reset(['nombre_menu', 'ruta_menu', 'acciones', 'modelo_menu', 'nombre_menu_eliminar', 'nombre_menu_estado']);
        $this->acciones = [];
        $this->resetErrorBag();
    }

    #[Computed()]
    public function acciones_menu()
    {
        // Devolver todas las acciones disponibles para el select en el modal.
        // Intentar varias formas de localizar el catálogo padre "ACCION" para
        // ser más tolerantes a diferencias en la base de datos (p. ej. "ACCIONES").
        try {
            $padre = $this->catalogo_service->buscarPadre(CatalogoEnum::ACCION->value);
        } catch (\Throwable $e) {
            // En caso de error en el servicio, devolver colección vacía para no romper el modal
            return collect();
        }

        return $padre?->hijos ?? collect();
    }

    public function render()
    {
        return view('livewire.seguridad.Menu.index');
    }
}
