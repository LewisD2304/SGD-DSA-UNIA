<?php

namespace App\Repositories\Seguridad\Usuario;

use App\Models\Usuario;

interface UsuarioRepositoryInterface
{
    //FUNCIONES QUE VA A TENER EL USUARIO
    public function listar();
    // Encontrar un usuario por id
    public function obtenerPorId(int $id, array $relaciones = []);

    // Listar usuarios habilitadas
    public function buscarHabilitados($buscar, $limite = null);

    // Listado con paginación y buscador
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden, string $orden = 'asc', array $relaciones = []);

    public function buscar(?string $buscar);

    // Registrar un nuevo usuario
    public function registrar(array $datos);

    // Modificar un usuario
    public function modificar(array $datos, Usuario $usuario);

    // Eliminar un usuario
    public function eliminar(Usuario $usuario);

    //Verifica si un modelo tiene relaciones existentes.
    public function verificarRelaciones(Usuario $modulo, array $relaciones);

    // Buscar usuario por nombre de usuario
    public function buscarPorNombreUsuario(string $nombre_usuario): ?Usuario;

    // Autenticar un usuario y retornar el modelo autenticado
    public function autenticar(string $nombre_usuario, string $password): ?Usuario;

    // Verificar si un usuario existe por nombre de usuario
    public function existePorNombreUsuario(string $nombreUsuario): bool;

    // Verificar si un usuario tiene un permiso
    public function verificarPermiso(int $id_usuario, string $accion, string $menu, ?string $modulo = null);
}
