<?php

namespace App\Repositories\Configuracion\Catalogo;

use App\Enums\EstadoEnum;
use App\Models\Catalogo;

class CatalogoRepository implements CatalogoRepositoryInterface
{
    // Listar todos los catálogos
    public function listar()
    {
        return Catalogo::all();
    }

    // Obtener por nombre y devolver el id
    public function obtenerIdPorNombre($nombre)
    {
        return Catalogo::where('descripcion_catalogo', $nombre)->first()->id_catalogo;
    }

    // Encontrar un catálogo por id
    public function obtenerPorId($id)
    {
        return Catalogo::findOrFail($id);
    }

    // Listar catálogos paginados con relaciones precargadas
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden, string $orden = 'asc', array $relaciones = [])
    {
        return Catalogo::query()
            ->with($relaciones)
            ->buscar($buscar)
            ->orderBy($columnaOrden, $orden)
            ->paginate($paginado);
    }

    // Listar catálogos padre con relaciones precargadas
    public function listarPadre($relaciones = [], $buscar = null, string $columna_orden, string $orden = 'asc')
    {
        return Catalogo::query()
            ->with($relaciones)
            ->where('id_padre', null)
            ->buscar($buscar)
            ->orderBy($columna_orden, $orden)
            ->get();
    }

    // Listar catálogos hijos de un catálogo padre
    public function listarHijos($id_padre, $relaciones = [], $paginado = 10, string $columna_orden, string $orden = 'asc')
    {
        // Si el paginado es 0, devolver todos los hijos sin paginación
        if($paginado === 0) {
            return Catalogo::query()
                ->with($relaciones)
                ->where('id_padre', $id_padre)
                ->get();
        } else {
            return Catalogo::query()
                ->with($relaciones)
                ->where('id_padre', $id_padre)
                ->orderBy($columna_orden, $orden)
                ->paginate($paginado);
        }
    }

    // Buscar catálogos por coincidencia
    public function buscar($buscar)
    {
        return Catalogo::buscar($buscar)->get();
    }

    // Buscar padre por abreviatura y sus hijos
    public function buscarPadre($abreviatura)
    {
        return Catalogo::with('hijos')
            ->where('abreviatura_catalogo', $abreviatura)
            ->where('id_padre', null)
            ->estado(EstadoEnum::HABILITADO)
            ->first();
    }

    // Buscar hijos en un rango de ids
    public function buscarHijosEnRango(array $ids, $relaciones = [])
    {
        return Catalogo::with($relaciones)
            ->whereIn('id_catalogo', $ids)
            ->get();
    }

     // Registrar un nuevo catálogo
    public function registrar(array $datos)
    {
        return Catalogo::create($datos);
    }

    // Modificar un catálogo
    public function modificar(array $datos, Catalogo $catalogo)
    {
        return $catalogo->update($datos);
    }

    // Eliminar un catálogo
    public function eliminar(Catalogo $catalogo)
    {
        // Validar si el catálogo tiene hijos antes de eliminar
        if ($catalogo->hijos->isNotEmpty()) {
            throw new \Exception('El catálogo tiene registros hijos asociados');
        } elseif ($catalogo->acciones()->exists()) {
            throw new \Exception('El catálogo tiene acciones asociadas');
        }

        return $catalogo->delete();
    }





}
