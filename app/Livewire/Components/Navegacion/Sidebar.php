<?php

namespace App\Livewire\Components\Navegacion;

use Livewire\Component;
use App\Services\Seguridad\PermisoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Sidebar extends Component
{
    public $menusPermitidos = [];

    protected $permisoService;

    public function boot(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }

    public function mount()
    {
        $this->cargarMenusPermitidos();
    }

    private function cargarMenusPermitidos()
    {


        $usuario = Auth::id();

        if (!$usuario) {
            return;
        }

        // Definir todos los menús disponibles con sus nombres en la base de datos
        $menusDisponibles = [
            'MENÚ' => [
                'nombre' => 'Menú',
                'ruta' => 'seguridad.menu.index',
                'routePattern' => 'seguridad.menu'
            ],
            'USUARIOS' => [
                'nombre' => 'Usuarios',
                'ruta' => 'seguridad.usuario.index',
                'routePattern' => 'seguridad.usuario'
            ],
            'ROLES' => [
                'nombre' => 'Roles',
                'ruta' => 'seguridad.rol.index',
                'routePattern' => 'seguridad.rol'
            ],
            'PERSONAS' => [
                'nombre' => 'Personas',
                'ruta' => 'seguridad.persona.index',
                'routePattern' => 'seguridad.persona'
            ],
            'DOCUMENTO' => [
                'nombre' => 'Documentos',
                'ruta' => 'documentos.documento.index',
                'routePattern' => 'documentos.documento'
            ],
            'PENDIENTES' => [
                'nombre' => 'Pendientes',
                'ruta' => 'documentos.pendientes.index',
                'routePattern' => 'documentos.pendientes'
            ],
            'CATALOGO' => [
                'nombre' => 'Catalogo',
                'ruta' => 'seguridad.catalogo.index',
                'routePattern' => 'seguridad.catalogo'
            ],
            'REPORTES' => [
                'nombre' => 'Reportes',
                'ruta' => 'reportes.index',
                'routePattern' => 'reportes'
            ],
        ];

        // Filtrar menús según permisos (acción LISTAR)
        foreach ($menusDisponibles as $nombreMenu => $datos) {
            if (Gate::allows('autorizacion', ['LISTAR', $nombreMenu])) {
                $this->menusPermitidos[$nombreMenu] = $datos;
            }
        }
    }

    public function render()
    {
        return view('livewire.components.navegacion.sidebar');
    }
}
