<?php

namespace App\Livewire\Seguridad\Rol;

use App\Services\Seguridad\MenuService;
use App\Services\Seguridad\RolService;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Configuración de accesos | SARA')]

class ConfiguracionAcceso extends Component
{
    public $id_rol;

    public $modelo_rol;
    public $modelo_menu;
    public $menus = [];
    public $menusDisponibles;
    public $menus_seleccionados;

    public $acciones_seleccionadas = [];
    public $seleccionar_todo = false;

    public $nombre_menu_eliminar = '';

    protected RolService $rol_service;
    protected MenuService $menu_service;

    public function __construct()
    {
        $this->rol_service = resolve(RolService::class);
        $this->menu_service = resolve(MenuService::class);
    }

    public function updatedSeleccionarTodo($value)
    {
        if ($value) {
            $this->acciones_seleccionadas = array_fill_keys(array_keys($this->acciones_seleccionadas), true);
        } else {
            $this->acciones_seleccionadas = array_fill_keys(array_keys($this->acciones_seleccionadas), false);
        }
    }

    public function updatedAccionesSeleccionadas($value)
    {
        $this->seleccionar_todo = !empty($this->acciones_seleccionadas) && array_reduce($this->acciones_seleccionadas, fn($carry, $value) => $carry && $value, true);
    }

    #[On('abrir_modal_quitar')]
    public function abrir_modal_quitar($id_menu)
    {
        // Filtrar la colección eliminando solo el id deseado
        $this->menus_seleccionados = collect($this->menus_seleccionados)
            ->reject(fn($menu) => $menu == $id_menu)
            ->values();

        $this->dispatch('refrescarMenusSeleccionados', menus_seleccionados: $this->menus_seleccionados);
        $this->dispatch('refrescarMenus');
        $this->dispatch('cargando', cargando: 'false');
    }

    public function eliminarAcceso()
    {
        $mensaje_t = null;

        try {
            $this->rol_service->eliminarPermisosPorMenu($this->modelo_rol, $this->modelo_menu->id_menu);
            // Filtrar la colección eliminando solo el id deseado
            $this->menus_seleccionados = collect($this->menus_seleccionados)
                ->reject(fn($menu) => $menu == $this->modelo_menu->id_menu)
                ->values();

            $this->dispatch('refrescarMenusSeleccionados', menus_seleccionados: $this->menus_seleccionados);
            $this->dispatch('refrescarMenus');

            $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Permisos del acceso al menú eliminados correctamente', 'top', 'right');
        } catch (Exception $e) {
            $mensaje_t = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalAcceso('#modal-eliminar-acceso', 'hide');

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

    #[On('abrirModalEliminarPermiso')]
    public function abrirModalEliminarPermiso($id_menu)
    {
        $this->modelo_menu = $this->menu_service->obtenerPorId([], $id_menu);
        $this->nombre_menu_eliminar = $this->modelo_menu->nombre_menu;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalAcceso('#modal-eliminar-acceso', 'show');
    }

    public function guardarPermiso()
    {
        $mensaje_t = null;

        try {
            $this->rol_service->guardarPermisos($this->modelo_rol, $this->modelo_menu->id_menu, $this->acciones_seleccionadas);

            $this->dispatch('refrescarMenus');

            $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Permisos guardados correctamente', 'top', 'right');
        } catch (Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensaje_t = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalAcceso('#modal-permiso', 'hide');
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

    // Abrir modal y cargar datos del modal para registrar o modificar permisos
    #[On('abrirModalPermiso')]
    public function abrirModalPermiso($id_menu)
    {
        $this->limpiarModal();

        // Obtener el menú con sus hijos y acciones recursivamente
        $this->modelo_menu = $this->menu_service->obtenerPorId(['hijos.acciones.permisos', 'acciones.permisos'], $id_menu);

        // Obtener los permisos del rol en las acciones (menú y sus hijos)
        $menus_con_hijos = collect([$this->modelo_menu]);
        $this->acciones_seleccionadas = $this->obtenerAccionesSeleccionadasRecursivo($menus_con_hijos)->toArray();

        // Aplanar todos los menús jerárquicos con nivel
        $this->menus = $this->obtenerMenusRecursivo($menus_con_hijos);

        // Verificar si esta seleccionado todo
        $this->seleccionar_todo = !empty($this->acciones_seleccionadas) && array_reduce($this->acciones_seleccionadas, fn($carry, $value) => $carry && $value, true);

        $this->dispatch('cargando', cargando: 'true');
        $this->modalAcceso('#modal-permiso', 'show');
    }

    private function obtenerAccionesSeleccionadasRecursivo($menus)
    {
        $acciones = collect();

        foreach ($menus as $menu) {
            foreach ($menu->acciones as $accion) {
                $acciones->put($accion->id_accion, $accion->permisos->contains('id_rol', $this->id_rol));
            }

            if ($menu->relationLoaded('hijos') && $menu->hijos->isNotEmpty()) {
                $acciones_hijos = $this->obtenerAccionesSeleccionadasRecursivo($menu->hijos);
                $acciones = $acciones->union($acciones_hijos);
            }
        }

        return $acciones;
    }

    function obtenerMenusRecursivo($menus, $nivel = 0)
    {
        $resultado = collect();

        foreach ($menus as $menu) {
            $resultado->push([
                'id_menu' => $menu->id_menu,
                'nombre_menu' => $menu->nombre_menu,
                'acciones' => $menu->acciones,
                'nivel' => $nivel
            ]);

            if ($menu->relationLoaded('hijos') && $menu->hijos->isNotEmpty()) {
                $resultado = $resultado->merge($this->obtenerMenusRecursivo($menu->hijos, $nivel + 1));
            }
        }

        return $resultado;
    }

    public function guardarAcceso()
    {
        try {
            $this->convertirMenusSeleccionados($this->menus_seleccionados);

            $menus_anteriores = $this->modelo_rol
                ->permisos()
                ->with('accion')
                ->get()
                ->pluck('accion.id_menu')
                ->unique()
                ->values();


            $menus_nuevos = $this->menus_seleccionados->diff($menus_anteriores);

            foreach ($menus_nuevos as $id_menu) {

                $menu = $this->menu_service->obtenerPorId(['acciones.tipoAccion'], $id_menu);

                if (!$menu || !$menu->acciones || $menu->acciones->isEmpty()) {
                    continue;
                }

                // buscar acción "LISTAR"
                $accion_listar = $menu->acciones->first(
                    fn($a) => $a->tipoAccion && $a->tipoAccion->descripcion_catalogo === 'LISTAR'
                );

                if (!$accion_listar) {
                    $accion_listar = $menu->acciones->first();
                }

                $this->rol_service->guardarPermisos(
                    $this->modelo_rol,
                    $id_menu,
                    [$accion_listar->id_accion => true]
                );
            }

            // actualizar lista de menús asignados
            $this->menus_seleccionados = $this->modelo_rol
                ->permisos()
                ->with('accion')
                ->get()
                ->pluck('accion.id_menu')
                ->unique()
                ->values();

            // refrescar vistas
            $this->dispatch('refrescarMenusSeleccionados', menus_seleccionados: $this->menus_seleccionados);
            $this->dispatch('refrescarMenus');
            $this->dispatch('refrescar_menusDisponibles');

            $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Accesos guardados correctamente', 'top', 'right');
        } catch (Exception $e) {
            $mensaje_t = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modalAcceso('#modal-acceso', 'hide');

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

    // Abrir modal y cargar datos del modal para registrar o modificar un acessos
    #[On('abrirModalAcceso')]
    public function abrirModalAcceso($menus_seleccionados)
    {
        $this->menus_seleccionados = collect($menus_seleccionados ?? []);
        $this->dispatch('cargando', cargando: 'false');
        $this->modalAcceso('#modal-acceso', 'show');
    }

    // Convertir a int los id de los menus seleccionados
    public function convertirMenusSeleccionados($menus_seleccionados)
    {
        $this->menus_seleccionados = collect($menus_seleccionados)->map(function ($menu) {
            return (int) $menu;
        });
    }

    #[On('abrirModalEliminarMenu')]
    public function abrirModalEliminarMenu($id_menu)
    {
        $this->limpiarModal();
        $this->modelo_menu = $this->menu_service->obtenerPorId([], $id_menu);
        $this->nombre_menu_eliminar = $this->modelo_menu->nombre_menu;

        $this->dispatch('cargando', cargando: 'false');
        $this->modalAcceso('#modal-eliminar-acceso', 'show');
    }

    // Abrir o cerrar modal
    public function modalAcceso($nombre, $accion)
    {
        $this->dispatch(
            'modal',
            nombre: $nombre,
            accion: $accion
        );
    }

    public function limpiarModal()
    {
        $this->menus_seleccionados = [];
        $this->acciones_seleccionadas = [];
        $this->menus = [];
    }

    public function mount()
    {
        $this->id_rol = desencriptar($this->id_rol);
        // Listar TODOS los menús del sistema (como el proyecto de referencia con módulos)
        $this->menusDisponibles = $this->menu_service->listar();
        // Obtener el modelo del rol
        $this->modelo_rol = $this->rol_service->obtenerPorId($this->id_rol);
        // Obtener los IDs de los menús ya asignados al rol
        $this->menus_seleccionados = collect($this->rol_service->obtenerMenusAsignadosConPermisos($this->id_rol))->pluck('id_menu');
    }

    public function render()
    {
        return view('livewire.seguridad.rol.configuracion-acceso');
    }
}
