<?php

namespace App\Repositories\Seguridad\Usuario;

use App\Models\Usuario;

interface UsuarioRepositoryInterface
{
   //FUNCIONES QUE VA A TENER EL USUARIO
    public function listar();
     // Encontrar un usuario por id
    public function obtenerPorId(int $id, array $relaciones = []);

    // Listado con paginación y buscador
    public function listarPaginado(int $paginado = 10, ?string $buscar = null,string $columnaOrden, string $orden = 'asc', array $relaciones = []);

    public function buscar(?string $buscar);

    // Registrar un nuevo usuario
    public function registrar(array $datos);

    // Modificar un usuario
    public function modificar(array $datos, Usuario $usuario);

    // Eliminar un usuario
    public function eliminar(Usuario $usuario);

    //Verifica si un modelo tiene relaciones existentes.
    public function verificarRelaciones(Usuario $modulo, array $relaciones);

    // Verificar si un usuario existe por nombre de usuario
    public function existePorNombreUsuario(string $nombreUsuario): bool;
}
