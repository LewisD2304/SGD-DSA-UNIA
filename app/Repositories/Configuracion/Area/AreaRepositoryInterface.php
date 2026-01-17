<?php

namespace App\Repositories\Configuracion\Area;

use App\Models\Area;

interface AreaRepositoryInterface
{
    public function listar();
    public function listarHabilitados();
    public function obtenerPorId(int $id, array $relaciones = []);
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden, string $orden = 'asc', array $relaciones = []);
    public function buscar(?string $buscar);
    public function registrar(array $datos);
    public function modificar(array $datos, Area $area);
    public function eliminar(Area $area);
    public function existePorNombre(string $nombreArea): bool;
    public function verificarRelaciones(Area $area, array $relaciones);
    public function sincronizarPersonas(Area $area, array $idsPersonas): void;
}
