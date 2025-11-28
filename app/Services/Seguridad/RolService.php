<?php

namespace App\Services\Seguridad;

use App\Models\Rol;
use App\Repositories\Seguridad\Rol\RolRepositoryInterface;
use Illuminate\Support\Facades\DB;

class RolService
{

    public function __construct(private RolRepositoryInterface $repository) {}

    // Listar todos los usuarios
    public function listar()
    {
        return $this->repository->listar();
    }

    // Encontrar un usuario por id
    public function obtenerPorId(int $id, array $relaciones = [])
    {
        return $this->repository->obtenerPorId($id, $relaciones);
    }

    // Listar usuarios paginados con relaciones precargadas y búsqueda
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_rol', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPaginado($paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    // Buscar usuarios por coincidencia
    public function buscar(?string $buscar)
    {
        return $this->repository->buscar($buscar);
    }

    // Registrar un nuevo usuario
    public function registrar(array $datos)
    {
        DB::beginTransaction();

        try {
            $existe_rol = $this->repository->existePorNombreRol($datos['nombre_rol']);

            if ($existe_rol) {
                throw new \Exception('El nombre de rol ya está en uso.');
            }

            // Registrar el rol

            $rol = $this->repository->registrar($datos);

            DB::commit();

            return $rol;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al registrar el rol.'.$e->getMessage());
        }
    }

    // Modificar un rol
    public function modificar(array $datos, Rol $rol)
    {
        DB::beginTransaction();

        try {

            $rol = $this->repository->modificar($datos, $rol);

            DB::commit();
            return $rol;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al modificar el rol.');
        }
    }

    // Cambiar el estado de un rol
    public function cambiarEstado(Rol $rol, $estado)
    {
        DB::beginTransaction();

        try {
            // Cambiar el estado del rol
            $rol = $this->repository->modificar(['estado_rol' => $estado], $rol);
            DB::commit();
            return $rol;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al cambiar el estado del rol');
        }
    }

    // Eliminar un rol
    public function eliminar(Rol $rol, array $relaciones = [])
    {
        DB::beginTransaction();

        try {
            if ($this->repository->verificarRelaciones($rol, $relaciones)) {
                throw new \Exception('No se puede eliminar el rol porque tiene relaciones existentes.');
            }

            $this->repository->eliminar($rol);

            DB::commit();
            return $rol;
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            throw new \Exception('Ocurrió un error al eliminar rol');
        }
    }
}
