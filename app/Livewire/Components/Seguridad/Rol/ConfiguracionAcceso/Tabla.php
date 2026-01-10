<?php

namespace App\Livewire\Components\Seguridad\Rol\ConfiguracionAcceso;

use App\Services\Seguridad\MenuService;
use App\Services\Seguridad\RolService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Tabla extends Component
{
    public $id_rol;
    public $modelo_rol;

    public $menus_seleccionados;

    protected RolService $rol_service;
    protected MenuService $menu_service;
    protected $paginationTheme = 'bootstrap';

    public function __construct()
    {
        $this->rol_service = resolve(RolService::class);
        $this->menu_service = resolve(MenuService::class);
    }

    #[On('refrescarMenusSeleccionados')]
    public function refrescarMenusSeleccionados($menus_seleccionados)
    {
        $this->menus_seleccionados = collect($menus_seleccionados ?? [])->values();
    }

    public function placeholder()
    {
        return <<<'HTML'
            <div>
                <div class="app-container container-fluid">
                    <div class="card placeholder-glow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="placeholder col-4" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>

                            <div class="card-toolbar gap-2">
                                <button
                                    type="button"
                                    class="btn btn-light btn-active-light-primary px-4 px-md-6 me-2 me-md-4"
                                    disabled
                                >
                                    <i class="ki-outline ki-arrow-left fs-4 px-0"></i>
                                    <span class="d-none d-md-inline">
                                        Regresar
                                    </span>
                                </b>

                                <button
                                    type="button"
                                    class="btn btn-primary px-4 px-md-6"
                                    disabled
                                >
                                    <i class="ki-outline ki-plus fs-2 px-0"></i>
                                    <span class="d-none d-md-inline">
                                        Asignar
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="card-body py-4">
                            <div lass="dataTables_wrapper dt-bootstrap4 no-footer">
                                <div class="table-responsive position-relative">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                                <th class="min-w-125px">NOMBRE DEL MENÚ</th>
                                                <th class="min-w-125px">FECHA ASIGNADO</th>
                                                <th class="text-center min-w-60px">ACCIÓN</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="text-gray-600"
                                            wire:loading.class="opacity-25"
                                            wire:target="buscar, gotoPage, previousPage, nextPage"
                                        >
                                            <tr>
                                                <td>
                                                    <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                                </td>
                                                <td>
                                                    <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                                </td>
                                                <td>
                                                    <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                                </td>
                                                <td>
                                                    <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                                </td>
                                                <td>
                                                    <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                                </td>
                                                <td>
                                                    <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                                </td>
                                                <td>
                                                    <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        HTML;
    }


    #[Computed()]
    #[On('refrescarMenus')]
    public function menusAsignados()
    {
        // Menús YA asignados al rol con sus permisos y fechas (para la tabla principal)
        return $this->rol_service->obtenerMenusAsignadosConPermisos($this->id_rol);
    }

    #[Computed()]
    public function menusDisponibles()
    {
        // TODOS los menús del sistema (para el modal de asignación)
        return $this->menu_service->listar();
    }

    public function abrirModalAcceso()
    {
        // Dispara un evento para abrir el modal
        $this->dispatch('abrirModalAcceso');
    }

    public function mount()
    {
        // Obtener el modelo del rol
        $this->modelo_rol = $this->rol_service->obtenerPorId($this->id_rol);

        // Menús ya seleccionados/asignados para el rol
        $this->menus_seleccionados = collect($this->rol_service->obtenerMenusAsignadosConPermisos($this->id_rol))->pluck('id_menu');
    }


    public function render()
    {
        return view('livewire.components.seguridad.rol.configuracion-acceso.tabla');
    }
}
