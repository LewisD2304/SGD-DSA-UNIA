<?php

namespace App\Repositories\Seguridad\Usuario;

class UsuarioRepositoryInterface
{
   //FUNCIONES QUE VA A TENER EL USUARIO

    public function listar();
    public function obtenerPorId(int $id, array $relaciones = []);
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string  array $relaciones = []);
}
