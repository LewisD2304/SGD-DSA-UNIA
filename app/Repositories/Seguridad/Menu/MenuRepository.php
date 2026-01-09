<?php

namespace App\Repositories\Seguridad\Menu;

use App\Enums\EstadoEnum;
use App\Models\Accion;
use App\Models\Menu;
use App\Traits\BaseRepositoryTrait;
use Illuminate\Support\Facades\DB;

class MenuRepository implements MenuRepositoryInterface
{
    use BaseRepositoryTrait;
    protected $model;

    public function __construct(Menu $menu)
    {
        $this->model = $menu;
    }

      // Obtener id de un menu por ruta
    public function obtenerConHijosPorRuta($ruta)
    {
        return $this->model
            ->with('padre.hijos')
            ->where('ruta_menu', $ruta)
            ->estado(EstadoEnum::HABILITADO)
            ->first();
    }

    // Listar acciones de un menu por nombre de menu
    public function listarAccionesPorNombreMenu($nombre_menu)
    {
        return $this->model::query()
            ->whereRaw('UPPER(TRIM(nombre_menu)) = ?', [strtoupper(trim($nombre_menu))])
            ->estado(EstadoEnum::HABILITADO)
            ->with(['acciones', 'acciones.tipoAccion'])
            ->first();
    }

    public function verificarAccionesEliminables(Menu $menu, array $acciones)
    {
        // Obtener los IDs de las acciones a eliminar dentro del menú
        $acciones_a_eliminar = Accion::where('id_menu', $menu->id_menu)
            ->whereIn('accion_catalogo', $acciones)
            ->pluck('id_accion');

        if ($acciones_a_eliminar->isEmpty()) {
            return false;
        }

        // Verificar si alguna de las acciones tiene permisos asociados
        $tiene_permisos = DB::table('ta_permiso')
            ->whereIn('id_accion', $acciones_a_eliminar)
            ->exists();

        if ($tiene_permisos) {
            return true;
        }
    }

    // Eliminar acciones que están en la lista
    public function eliminarAcciones(Menu $menu, array $acciones)
    {
        // Obtener los ids de las acciones, con los ids de los tipos de acciones
        $acciones = Accion::query()
                    ->whereIn('accion_catalogo', $acciones)
                    ->where('id_menu', $menu->id_menu)
                    ->pluck('id_accion')
                    ->toArray();

        return Accion::query()
            ->whereIn('id_accion', $acciones)
            ->delete();
    }

}
