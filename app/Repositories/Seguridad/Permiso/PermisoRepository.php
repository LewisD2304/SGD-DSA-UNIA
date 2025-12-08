<?php

namespace App\Repositories\Seguridad\Permiso;

use App\Models\Permiso;
use App\Models\Accion;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class PermisoRepository implements PermisoRepositoryInterface
{
    protected $model;

    public function __construct(Permiso $permiso)
    {
        $this->model = $permiso;
    }

    /**
     * Verificar si un rol tiene permiso para una acción específica en un menú
     */
    public function verificarPermiso(int $id_rol, string $accion, string $menu): bool
    {
        return $this->model::where('id_rol', $id_rol)
            ->whereHas('accion', function ($query) use ($accion, $menu) {
                $query->whereHas('tipoAccion', function ($q) use ($accion) {
                    $q->where('descripcion_catalogo', strtoupper($accion));
                })
                ->whereHas('menu', function ($q) use ($menu) {
                    $q->where('nombre_menu', strtoupper($menu));
                });
            })
            ->exists();
    }

    /**
     * Obtener todos los permisos de un rol agrupados por menú
     */
    public function obtenerPermisosPorRol(int $id_rol)
    {
        return $this->model::where('id_rol', $id_rol)
            ->with(['accion.menu', 'accion.tipoAccion'])
            ->get()
            ->groupBy('accion.id_menu');
    }

    /**
     * Guardar permisos de un rol para un menú específico
     * @param int $id_rol ID del rol
     * @param int $id_menu ID del menú
     * @param array $acciones_seleccionadas Array de id_accion seleccionados
     */
    public function guardarPermisos(int $id_rol, int $id_menu, array $acciones_seleccionadas)
    {
        DB::beginTransaction();

        try {
            // 1) Eliminar permisos existentes del rol para este menú
            $this->eliminarPermisosPorMenu($id_rol, $id_menu);

            $permisos = [];

            foreach ($acciones_seleccionadas as $key => $value) {

                // Caso 1: array del tipo [ id_accion => true ]
                if (is_numeric($key)) {
                    $id_accion = (int) $key;
                    $seleccionado = (bool)$value;
                }
                // Caso 2: array del tipo [ 0 => id_accion ]
                else {
                    $id_accion = (int)$value;
                    $seleccionado = true;
                }

                if (!$seleccionado) continue;
                if ($id_accion <= 0) continue;
                if (!Accion::where('id_accion', $id_accion)->exists()) continue;

                $permisos[] = [
                    'id_accion' => $id_accion,
                    'id_rol'    => $id_rol,
                ];
            }

            if (!empty($permisos)) {
                $this->model::insert($permisos);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Eliminar todos los permisos de un rol para un menú específico
     */
    public function eliminarPermisosPorMenu(int $id_rol, int $id_menu)
    {
        return $this->model::whereHas('accion', function ($query) use ($id_menu) {
            $query->where('id_menu', $id_menu);
        })
            ->where('id_rol', $id_rol)
            ->delete();
    }

    /**
     * Listar todos los permisos
     */
    public function listar()
    {
        return $this->model::with(['accion.menu', 'accion.tipoAccion', 'rol'])->get();
    }

    /**
     * Registrar un nuevo permiso
     */
    public function registrar(array $datos)
    {
        return $this->model::create($datos);
    }

    /**
     * Eliminar un permiso
     */
    public function eliminar($permiso)
    {
        return $permiso->delete();
    }
}
