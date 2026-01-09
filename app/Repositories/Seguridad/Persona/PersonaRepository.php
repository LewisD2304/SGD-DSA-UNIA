<?php

namespace App\Repositories\Seguridad\Persona;

use App\Enums\EstadoEnum;
use App\Models\Persona;
use App\Traits\BaseRepositoryTrait;

class PersonaRepository implements PersonaRepositoryInterface
{
    use BaseRepositoryTrait;
    protected $model;

    public function __construct(Persona $persona)
    {
        $this->model = $persona;
    }

    // Listar personas habilitadas
    public function listarHabilitados()
    {
        return $this->model::estado(EstadoEnum::HABILITADO)
            ->orderBy('nombres_persona')
            ->get();
    }

    // Verificar si una persona existe por nombre de persona
    public function existePorNombrePersona(string $nombresPersona): bool
    {
        return $this->model::where('nombres_persona', $nombresPersona)->exists();
    }
}
