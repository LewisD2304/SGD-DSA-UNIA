<?php

namespace App\Repositories\Seguridad\Persona;

use App\Models\Persona;

interface PersonaRepositoryInterface
{
    // Listar todos los registros
    public function listar();

    // Listar personas habilitadas
    public function listarHabilitados();

    // Encontrar un registro por id
    public function obtenerPorId(int $id, array $relaciones = []);

    // Listar personas paginados con relaciones precargadas
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden, string $orden = 'asc', array $relaciones = []);

    // Buscar registros por coincidencia
    public function buscar(?string $buscar);

    // Registrar un nuevo registro
    public function registrar(array $datos);

    // Modificar un registro
    public function modificar(array $datos, Persona $persona);

    // Eliminar un registro
    public function eliminar(Persona $persona);

    //Verifica si un modelo tiene relaciones existentes.
    public function verificarRelaciones(Persona $persona, array $relaciones);

    // Verificar si una persona existe por nombre de persona
    public function existePorNombrePersona(string $nombresPersona): bool;
}
