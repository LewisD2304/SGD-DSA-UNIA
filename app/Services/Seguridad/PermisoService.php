<?php

namespace App\Services\Seguridad;

use App\Models\Usuario;
use App\Repositories\Seguridad\Permiso\PermisoRepositoryInterface;

class PermisoService
{
    public function __construct(private PermisoRepositoryInterface $repository)
    {}

    /**
     * Verificar si un usuario puede acceder a una acción específica en un menú
     * @param Usuario $usuario Usuario autenticado
     * @param string $accion Nombre de la acción (LISTAR, REGISTRAR, MODIFICAR, etc.)
     * @param string $menu Nombre del menú
     * @return bool True si tiene permiso, false si no
     */
    public function puedeAcceder(Usuario $usuario, string $accion, string $menu): bool
    {
        // Si no tiene rol asignado, no tiene permisos
        if (!$usuario->id_rol) {
            return false;
        }

        // Normalizar: convertir guiones bajos a espacios para que coincida con la BD
        $accion_normalizada = str_replace('_', ' ', strtoupper($accion));
        $menu_normalizado = strtoupper($menu);

        return $this->repository->verificarPermiso($usuario->id_rol, $accion_normalizada, $menu_normalizado);
    }

    /**
     * Obtener todos los permisos de un rol agrupados por menú
     */
    public function obtenerPermisosPorRol(int $id_rol)
    {
        return $this->repository->obtenerPermisosPorRol($id_rol);
    }

    /**
     * Guardar permisos de un rol para un menú específico
     */
    public function guardarPermisos(int $id_rol, int $id_menu, array $acciones_seleccionadas)
    {
        return $this->repository->guardarPermisos($id_rol, $id_menu, $acciones_seleccionadas);
    }

    /**
     * Eliminar todos los permisos de un rol para un menú específico
     */
    public function eliminarPermisosPorMenu(int $id_rol, int $id_menu)
    {
        return $this->repository->eliminarPermisosPorMenu($id_rol, $id_menu);
    }
}
