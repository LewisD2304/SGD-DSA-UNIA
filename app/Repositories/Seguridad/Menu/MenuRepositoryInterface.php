<?php

namespace App\Repositories\Seguridad\Menu;

use App\Models\Menu;

interface MenuRepositoryInterface
{
// Listar todos los menus
    public function listar();

    // Registrar un nuevo menu
    public function registrar(array $datos);

    // Modificar un menu
    public function modificar(array $datos, Menu $menu);

    // Eliminar un menu
    public function eliminar(Menu $menu);

    // Listar con relaciones precargadas
    public function listarConRelaciones(array $relaciones = []);

    // Listar acciones de un menu por nombre de menu
    public function listarAccionesPorNombreMenu($nombre_menu);

    // Encontrar un menu por id
    public function obtenerPorId(int $id, array $relaciones = []);

     // Listar roles paginados con relaciones precargadas
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden, string $orden = 'asc', array $relaciones = []);

    // Obtener id de un menu por ruta
    public function obtenerConHijosPorRuta(string $ruta);

    // Buscar menus por coincidencia
    public function buscar(?string $buscar);

    //Verifica si un modelo tiene relaciones existentes.
    public function verificarRelaciones(Menu $menu, array $relaciones);

    // Verificar relaciones antes de eliminar
    public function verificarAccionesEliminables(Menu $menu, array $acciones);

    // Eliminar acciones que están en la lista
    public function eliminarAcciones(Menu $menu, array $acciones);
}
