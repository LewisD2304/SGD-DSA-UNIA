<?php

namespace App\Repositories\Configuracion\Area;

use App\Models\Area;

class AreaRepository implements AreaRepositoryInterface
{
    protected $model;

    public function __construct(Area $area)
    {
        $this->model = $area;
    }

    public function listar()
    {
        return $this->model::orderBy('nombre_area')->get();
    }

    public function listarActivas()
    {
        return $this->model::orderBy('nombre_area')->get();
    }

    public function obtenerPorId(int $id)
    {
        return $this->model::find($id);
    }
}
