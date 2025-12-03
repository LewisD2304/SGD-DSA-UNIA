<?php

namespace App\Repositories\Configuracion\Catalogo;

use App\Models\Catalogo;

interface CatalogoRepositoryInterface
{
   // Listar todos los catálogos
    public function listar();

    // Obtener por nombre y devolver el id
    public function obtenerIdPorNombre($nombre);

    // Encontrar un catálogo por id
    public function obtenerPorId($id);

    // Listar roles paginados con relaciones precargadas
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden, string $orden = 'asc', array $relaciones = []);

    // Listar catálogos padre con relaciones precargadas
    public function listarPadre($relaciones = [], $buscar = null, string $columna_orden, string $orden = 'asc');

    // Listar catálogos hijos de un catálogo padre
    public function listarHijos($id_padre, $relaciones = [], $paginado = 10, string $columna_orden, string $orden = 'asc');

    // Buscar catálogos por coincidencia
    public function buscar($buscar);

    // Buscar padre por abreviatura
    public function buscarPadre($abreviatura);

    // Buscar hijos en un rago de ids
    public function buscarHijosEnRango(array $ids, $relaciones = []);

    // Registrar un nuevo catálogo
    public function registrar(array $datos);

    // Modificar un catálogo
    public function modificar(array $datos, Catalogo $catalogo);

    // Eliminar un catálogo
    public function eliminar(Catalogo $catalogo);
}
