<?php

namespace App\Repositories\Seguridad\Rol;

use App\Enums\EstadoEnum;
use App\Models\Rol;
use App\Traits\BaseRepositoryTrait;

class RolRepository implements RolRepositoryInterface
{
    use BaseRepositoryTrait;
    protected $model;

    public function __construct(Rol $rol)
    {
        $this->model = $rol;
    }

    // Listar roles habilitados
    public function listarHabilitados()
    {
        return $this->model::estado(EstadoEnum::HABILITADO)
            ->orderBy('nombre_rol')
            ->get();
    }

    // Verificar si un rol existe por nombre de rol
    public function existePorNombreRol(string $nombreRol): bool
    {
        return $this->model::where('nombre_rol', $nombreRol)->exists();
    }
}
