<?php

namespace App\Services\Configuracion;

use App\Repositories\Configuracion\Area\AreaRepositoryInterface;

class AreaService
{
    public function __construct(private AreaRepositoryInterface $repository) {}

    public function listar()
    {
        return $this->repository->listar();
    }

    public function listarActivas()
    {
        return $this->repository->listarActivas();
    }

    public function obtenerPorId(int $id)
    {
        return $this->repository->obtenerPorId($id);
    }
}
