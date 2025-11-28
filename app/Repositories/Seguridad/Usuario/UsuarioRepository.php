<?php

namespace App\Repositories\Seguridad\Usuario;

use App\Models\Usuario;
use App\Traits\BaseRepositoryTrait;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    //TODAS LAS CONSULTAS SE VAN HACER ACA, NADA EN EL CONTROLADOR !!! =D
    //EN EL SERVICIO ESTÁ TODA LA LOGICA

    use BaseRepositoryTrait;
    protected $model;

    public function __construct(Usuario $usuario)
    {
        $this->model = $usuario;
    }

    // Verificar si un usuario existe por nombre de usuario
    public function existePorNombreUsuario(string $nombreUsuario): bool
    {
        return $this->model::where('nombre_usuario', $nombreUsuario)->exists();
    }








}
