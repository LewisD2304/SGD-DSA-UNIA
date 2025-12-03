<?php

namespace App\Services\Configuracion\Catalogo;


use App\Models\Catalogo;
use App\Repositories\Configuracion\Catalogo\CatalogoRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CatalogoService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private CatalogoRepositoryInterface $repository)
    {}

    // Listar todos los catálogos
    public function listar()
    {
        return $this->repository->listar();
    }

    // Obtener por nombre y devolver el id
    public function obtenerIdPorNombre($nombre)
    {
        return $this->repository->obtenerIdPorNombre($nombre);
    }

    // Encontrar un catálogo por id
    public function obtenerPorId($id)
    {
        return $this->repository->obtenerPorId($id);
    }

    // Listar usuarios paginados con relaciones precargadas y búsqueda
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_catalogo', string $orden = 'asc', array $relaciones = [])
    {
        return $this->repository->listarPaginado($paginado, $buscar, $columnaOrden, $orden, $relaciones);
    }

    // Listar catálogos padre con relaciones precargadas
    public function listarPadre($relaciones = [], $buscar = null, string $columna_orden = 'id_catalogo', string $orden = 'asc')
    {
        return $this->repository->listarPadre($relaciones, $buscar, $columna_orden, $orden);
    }

    public function listarHijos($id_padre, $relaciones = [], $paginado = 10, string $columna_orden = 'id_catalogo', string $orden = 'asc')
    {
        return $this->repository->listarHijos($id_padre, $relaciones, $paginado, $columna_orden, $orden);
    }

    // Buscar catálogos por coincidencia
    public function buscar($buscar)
    {
        return $this->repository->buscar($buscar);
    }

    // Buscar padre por abreviatura
    public function buscarPadre($abreviatura)
    {
        return $this->repository->buscarPadre($abreviatura);
    }

    // Buscar hijos en un rago de ids
    public function buscarHijosEnRango(array $ids, $relaciones = [])
    {
        return $this->repository->buscarHijosEnRango($ids, $relaciones);
    }

    // Registrar un nuevo catálogo
    public function registrar(array $datos)
    {
        if (!Gate::allows('autorizacion', ['REGISTRAR', 'CATÁLOGO'])) {
            throw new \Exception(config('settings.mensaje_error_acceso') . 'registrar catálogos' . config('settings.mensaje_final_error_acceso'));
        }
        DB::beginTransaction();

        try {
            // Crear el catálogo
            $catalogo = $this->repository->registrar($datos);

            DB::commit();
            return $catalogo;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception('Ocurrió un error al registrar el catálogo'.$e->getMessage());
        }
    }

    // Modificar un catálogo
    public function modificar(array $datos, Catalogo $catalogo)
    {
        if (!Gate::allows('autorizacion', ['MODIFICAR', 'CATÁLOGO'])) {
            throw new \Exception(config('settings.mensaje_error_acceso') . 'modificar catálogos' . config('settings.mensaje_final_error_acceso'));
        }
        DB::beginTransaction();

        try {
            // Modificar el catálogo
            $this->repository->modificar($datos, $catalogo);

            DB::commit();
            return $catalogo;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception('Ocurrió un error al modificar el catálogo');
        }
    }

    // Cambiar el estado de un catálogo
    public function cambiarEstado(Catalogo $catalogo, $estado)
    {
        if (!Gate::allows('autorizacion', ['CAMBIAR ESTADO', 'CATÁLOGO'])) {
            throw new \Exception(config('settings.mensaje_error_acceso') . 'cambiar el estado de catálogos' . config('settings.mensaje_final_error_acceso'));
        }
        DB::beginTransaction();

        try {
            // Cambiar el estado del catálogo
            $catalogo = $this->repository->modificar(['estado_catalogo' => $estado], $catalogo);

            DB::commit();
            return $catalogo;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception('Ocurrió un error al cambiar el estado del catálogo'.e($e->getMessage()));
        }
    }

    // Eliminar un catálogo
    public function eliminar(Catalogo $catalogo)
    {
        if (!Gate::allows('autorizacion', ['ELIMINAR', 'CATÁLOGO'])) {
            throw new \Exception(config('settings.mensaje_error_acceso') . 'eliminar catálogos' . config('settings.mensaje_final_error_acceso'));
        }
        DB::beginTransaction();

        try {
            // Eliminar el catálogo
            $this->repository->eliminar($catalogo);

            DB::commit();
            return $catalogo;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception('Ocurrió un error al eliminar el catálogo'.e($e->getMessage()));
        }
    }
}
