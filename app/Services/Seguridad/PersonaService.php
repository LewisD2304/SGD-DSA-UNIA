<?php

namespace App\Services\Seguridad;

use App\Models\Persona;
use App\Repositories\Seguridad\Persona\PersonaRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PersonaService
{

    public function __construct(private PersonaRepositoryInterface $repository){}

    // Listar todos las personas
    public function listar()
    {
        return $this->repository->listar();
    }

    // Listar personas habilitadas
    public function listarHabilitados()
    {
        return $this->repository->listarHabilitados();
    }

    // Encontrar un usuario por id
    public function obtenerPorId(int $id, array $relaciones = [])
    {
        return $this->repository->obtenerPorId($id, $relaciones);
    }

    // Listar usuarios paginados con relaciones precargadas y búsqueda
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_persona', string $orden = 'asc', array $relaciones = [])
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
            $existe_persona = $this->repository->existePorNombrePersona($datos['nombres_persona']);

            if ($existe_persona) {
                throw new \Exception('El nombre de la persona ya está en uso.');
            }

            // Registrar la persona

            $persona = $this->repository->registrar($datos);

            DB::commit();

            return $persona;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al registrar la persona.'.$e->getMessage());
        }
    }

    // Modificar una persona
    public function modificar(array $datos, Persona $persona)
    {
        DB::beginTransaction();

        try {

            $persona = $this->repository->modificar($datos, $persona);

            DB::commit();
            return $persona;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al modificar la persona.');
        }
    }

    // Cambiar el estado de una persona
    public function cambiarEstado(Persona $persona, $estado)
    {
        DB::beginTransaction();

        try {
            // Cambiar el estado de la persona
            $persona = $this->repository->modificar(['estado_persona' => $estado], $persona);
            DB::commit();
            return $persona;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al cambiar el estado de la persona');
        }
    }

    // Eliminar una persona
    public function eliminar(Persona $persona, array $relaciones = [])
    {
        DB::beginTransaction();

        try {
            if ($this->repository->verificarRelaciones($persona, $relaciones)) {
                throw new \Exception('No se puede eliminar a la persona porque tiene relaciones existentes.');
            }

            $this->repository->eliminar($persona);

            DB::commit();
            return $persona;
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            throw new \Exception('Ocurrió un error al eliminar persona.'.$e->getMessage());
        }
    }


}
