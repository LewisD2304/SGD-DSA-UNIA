<?php

namespace App\Services\Seguridad;

use App\Models\Accion;
use App\Models\Menu;
use App\Repositories\Seguridad\Menu\MenuRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RelacionesMenuException extends \Exception {}

class MenuService
{

    public function __construct(private MenuRepositoryInterface $repository)
    {}

    // Listar todos los menus
    public function listar()
    {
        return $this->repository->listar();
    }

     // Listar acciones de un menu por nombre de menu
    public function listarAccionesPorNombreMenu($nombre_menu)
    {
        return $this->repository->listarAccionesPorNombreMenu($nombre_menu);
    }

    // Encontrar un menu por id
    public function obtenerPorId(array $relaciones = [], int $id)
    {
        return $this->repository->obtenerPorId($id, $relaciones);
    }

    // Listar menus paginados con relaciones precargadas y búsqueda
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_rol', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPaginado($paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    public function obtenerConHijosPorRuta(string $ruta)
    {
        return $this->repository->obtenerConHijosPorRuta($ruta);
    }

    // Buscar menus por coincidencia
    public function buscar(?string $buscar)
    {
        return $this->repository->buscar($buscar);
    }

    // Registrar un nuevo menu
    public function registrar(array $datos, array $acciones)
    {
        // if (!Gate::allows('autorizacion', ['REGISTRAR', 'MENÚ'])) {
        //     throw new \Exception(config('settings.mensaje_error_acceso') . 'registrar menús' . config('settings.mensaje_final_error_acceso'));
        // }
        DB::beginTransaction();

        try {
            // Registrar el menu
            $menu = $this->repository->registrar($datos);

            // Limpiar los campos vacíos o nulos
            $acciones = array_filter($acciones);

            // Transformar los IDs en un array asociativo con los campos correctos
            $acciones = array_map(fn($id) => ['accion_catalogo' => $id], $acciones);
            // $acciones = collect($acciones)->map(fn($id) => ['accion_catalogo' => $id])->toArray();

            // Registrar las acciones del menu
            if (!empty($acciones)) {
                $menu->acciones()->createMany($acciones);
            }

            DB::commit();
            return $menu;
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            throw new \Exception('Error en la base de datos al registrar el menú.'  . $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();
            // Lanzar una excepción
            throw new \Exception('Ocurrió un error al registrar el menú');
        }
    }

    // Modificar un menu
    public function modificar(array $datos, Menu $menu, array $acciones)
    {
        // if (!Gate::allows('autorizacion', ['MODIFICAR', 'MENÚ'])) {
        //     throw new \Exception(config('settings.mensaje_error_acceso') . 'modificar menús' . config('settings.mensaje_final_error_acceso'));
        // }
        DB::beginTransaction();

        try {
            // Modificar el menú y obtener el modelo actualizado con sus relaciones
            $menu = $this->repository->modificar($datos, $menu);

            // Obtener las acciones actuales del menú
            $acciones_actuales = $menu->acciones->pluck('accion_catalogo')->toArray();

            // Determinar las acciones a eliminar y agregar
            $acciones_eliminar = array_diff($acciones_actuales, $acciones);
            $acciones_agregar  = array_diff($acciones, $acciones_actuales);

            // Validar si alguna de las acciones a eliminar está relacionada en permiso
            // $acciones_en_permiso = $this->repository->verificarAccionesEliminables($menu, $acciones_eliminar);
            //
            // if ($acciones_en_permiso) {
            //     throw new RelacionesMenuException('No se pueden quitar acciones que están relacionadas en permisos');
            // }

            // Eliminar acciones que ya no están en la lista
            if ($acciones_eliminar) {
                $resp = $this->repository->eliminarAcciones($menu, $acciones_eliminar);
            }

            // Insertar nuevas acciones en un solo query
            if ($acciones_agregar) {
                $nuevas_acciones = array_map(fn($tipo) => [
                    'id_menu'    => $menu->id_menu,
                    'accion_catalogo' => $tipo
                ], $acciones_agregar);

                Accion::insert($nuevas_acciones);
            }

            DB::commit();
            return $menu;
        } catch (RelacionesMenuException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            throw new \Exception('Error en la base de datos al modificar el menú.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al modificar el menú.');
        }
    }


    // Cambiar el estado de un menu
    public function cambiarEstado(Menu $menu, $estado)
    {
        // if (!Gate::allows('autorizacion', ['CAMBIAR ESTADO', 'MENÚ'])) {
        //     throw new \Exception(config('settings.mensaje_error_acceso') . 'cambiar el estado de menús' . config('settings.mensaje_final_error_acceso'));
        // }
        DB::beginTransaction();

        try {
            // Si el nombre del menu es 'MENU', lanzar una excepción
            if (limpiarCadena($menu->nombre_menu) === 'MENU') {
                throw new \Exception('No se puede cambiar el estado del menú principal');
            }

            // Cambiar el estado del menu
            $menu = $this->repository->modificar(['estado_menu' => $estado], $menu);

            DB::commit();
            return $menu;
        } catch (\Exception $e) {
            DB::rollBack();
            // Lanzar una excepción
            throw new \Exception('Ocurrió un error al cambiar el estado del menú');
        }
    }

    // Eliminar un menu
    public function eliminar(Menu $menu)
    {
        // if (!Gate::allows('autorizacion', ['ELIMINAR', 'MENÚ'])) {
        //     throw new \Exception(config('settings.mensaje_error_acceso') . 'eliminar menús' . config('settings.mensaje_final_error_acceso'));
        // }
        DB::beginTransaction();

        try {
            // Si el nombre del menu es 'MENU', lanzar una excepción
            if (limpiarCadena($menu->nombre_menu) === 'MENU') {
                throw new \Exception('No se puede eliminar el menú principal');
            }
            //
            // // Validar si el menú tiene acciones relacionadas en permisos
            // if ($this->repository->verificarRelaciones($menu, $relaciones)) {
            //     throw new RelacionesMenuException('No se puede eliminar el menú porque tiene acciones relacionadas en permisos');
            // }

            // Eliminar el menu
            $menu = $this->repository->eliminar($menu);

            DB::commit();
            return $menu;
        } catch (RelacionesMenuException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            // Lanzar una excepción si no se puede eliminar
            throw new \Exception('Ocurrió un error al eliminar el menú');
        }
    }


}
