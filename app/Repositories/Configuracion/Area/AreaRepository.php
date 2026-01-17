<?php

namespace App\Repositories\Configuracion\Area;

use App\Enums\EstadoEnum;
use App\Models\Area;
use App\Traits\BaseRepositoryTrait;

class AreaRepository implements AreaRepositoryInterface
{
    use BaseRepositoryTrait;
    protected $model;

    public function __construct(Area $area)
    {
        $this->model = $area;
    }

    // Listar áreas habilitadas
    public function listarHabilitados()
    {
        return $this->model::estado(EstadoEnum::HABILITADO)
            ->orderBy('nombre_area')
            ->get();
    }

    // Verificar si un área existe por nombre
    public function existePorNombre(string $nombreArea): bool
    {
        return $this->model::where('nombre_area', $nombreArea)->exists();
    }

    // Sincronizar personas asignadas a un área
    public function sincronizarPersonas(Area $area, array $idsPersonas): void
    {
        // Si el array está vacío, desasignar TODAS las personas del área
        if (empty($idsPersonas)) {
            \App\Models\Persona::where('id_area', $area->id_area)
                ->update(['id_area' => null]);
            return;
        }

        // Desasignar personas que ya no están en la lista
        \App\Models\Persona::where('id_area', $area->id_area)
            ->whereNotIn('id_persona', $idsPersonas)
            ->update(['id_area' => null]);

        // Asignar nuevas personas al área
        \App\Models\Persona::whereIn('id_persona', $idsPersonas)
            ->update(['id_area' => $area->id_area]);
    }
}
