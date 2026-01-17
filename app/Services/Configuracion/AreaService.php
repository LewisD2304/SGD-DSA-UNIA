<?php

namespace App\Services\Configuracion;

use App\Models\Area;
use App\Repositories\Configuracion\Area\AreaRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AreaService
{
    public function __construct(private AreaRepositoryInterface $repository) {}

    // Obtener el repositorio
    public function getRepository(): AreaRepositoryInterface
    {
        return $this->repository;
    }

    // Listar todas las áreas
    public function listar()
    {
        return $this->repository->listar();
    }

    // Listar áreas habilitadas
    public function listarHabilitados()
    {
        return $this->repository->listarHabilitados();
    }

    // Alias para compatibilidad con código existente
    public function listarActivas()
    {
        return $this->listarHabilitados();
    }

    // Encontrar un área por id
    public function obtenerPorId(int $id, array $relaciones = [])
    {
        return $this->repository->obtenerPorId($id, $relaciones);
    }

    // Listar áreas paginadas con búsqueda
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_area', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPaginado($paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    // Buscar áreas por coincidencia
    public function buscar(?string $buscar)
    {
        return $this->repository->buscar($buscar);
    }

    // Registrar una nueva área
    public function registrar(array $datos)
    {
        DB::beginTransaction();

        try {
            if ($this->repository->existePorNombre($datos['nombre_area'])) {
                throw new \Exception('El nombre del área ya está en uso.');
            }

            $area = $this->repository->registrar($datos);

            DB::commit();

            return $area;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al registrar el área: ' . $e->getMessage());
        }
    }

    // Modificar una área
    public function modificar(array $datos, Area $area)
    {
        DB::beginTransaction();

        try {
            $area = $this->repository->modificar($datos, $area);

            DB::commit();
            return $area;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al modificar el área: ' . $e->getMessage());
        }
    }

    // Cambiar el estado de un área
    public function cambiarEstado(Area $area, $estado)
    {
        DB::beginTransaction();

        try {
            $area = $this->repository->modificar(['estado_area' => $estado], $area);
            DB::commit();
            return $area;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al cambiar el estado del área: ' . $e->getMessage());
        }
    }

    // Eliminar un área
    public function eliminar(Area $area, array $relaciones = [])
    {
        DB::beginTransaction();

        try {
            // Validar relaciones
            foreach ($relaciones as $relacion) {
                if ($area->$relacion()->count() > 0) {
                    throw new \Exception('No se puede eliminar el área porque tiene ' . $relacion . ' asociadas.');
                }
            }

            $this->repository->eliminar($area);

            DB::commit();
            return $area;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Ocurrió un error al eliminar el área: ' . $e->getMessage());
        }
    }

    // Asignar personas a un área
    public function asignarPersonas(Area $area, array $idsPersonas)
    {
        DB::beginTransaction();

        try {
            $this->repository->sincronizarPersonas($area, $idsPersonas);

            DB::commit();
            return $area;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al asignar personas al área: ' . $e->getMessage());
        }
    }
}
