<?php

namespace App\Services\Seguridad;

use App\Repositories\Seguridad\Usuario\UsuarioRepositoryInterface;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class UsuarioService
{

    public function __construct(private UsuarioRepositoryInterface $repository) {}

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
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_usuario', string $orden = 'asc', array $relaciones = [])
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
            $existe_usuario = $this->repository->existePorNombreUsuario($datos['nombre_usuario']);

            if ($existe_usuario) {
                throw new \Exception('El nombre de usuario ya está en uso.');
            }

            // Registrar el usuario
            $usuario = $this->repository->registrar($datos);

            DB::commit();

            return $usuario;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al registrar el usuario.');
        }
    }

    // Modificar un usuario
    public function modificar(array $datos, Usuario $usuario)
    {
        DB::beginTransaction();

        try {

            $usuario = $this->repository->modificar($datos, $usuario);

            DB::commit();
            return $usuario;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al modificar el usuario.');
        }
    }

    // Cambiar el estado de un usuario
    public function cambiar_estado(Usuario $usuario, $estado)
    {
        DB::beginTransaction();

        try {
            // Cambiar el estado del usuario
            $usuario = $this->repository->modificar(['estado_usuario' => $estado], $usuario);
            DB::commit();
            return $usuario;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al cambiar el estado del usuario');
        }
    }

    // Eliminar un usuario
    public function eliminar(Usuario $usuario)
    {
        DB::beginTransaction();

        try {
            $this->repository->eliminar($usuario);

            DB::commit();
            return $usuario;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al eliminar usuario');
        }
    }

}
