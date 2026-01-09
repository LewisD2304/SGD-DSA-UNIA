<?php

namespace App\Repositories\Configuracion\Area;

use App\Models\Area;

interface AreaRepositoryInterface
{
    public function listar();
    public function listarActivas();
    public function obtenerPorId(int $id);
}
