<?php

namespace App\Repositories\Documentos\Documento;

use App\Models\Documento;

interface DocumentoRepositoryInterface
{
    public function listar();
    public function obtenerPorId(int $id, array $relaciones = []);
    public function listarPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden, string $orden = 'asc', array $relaciones = []);
    public function listarPendientesPaginado(int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = []);
    public function listarPaginadoPorArea(int $idArea, int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = []);
    public function listarPaginadoPorAreaConFiltros(int $idArea, int $paginado = 10, ?string $buscar = null, ?string $fechaInicio = null, ?string $fechaFin = null, ?string $idEstado = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = []);
    public function listarPendientesPorArea(int $idArea, int $paginado = 10, ?string $buscar = null, string $columnaOrden = 'id_documento', string $orden = 'asc', array $relaciones = []);
    public function buscar(?string $buscar);
    public function registrar(array $datos);
    public function modificar(array $datos, Documento $documento);
    public function eliminar(Documento $documento);
    public function verificarRelaciones(Documento $documento, array $relaciones);
    public function existeExpediente(string $expediente): bool;
    public function generarExpediente(): string;
}
