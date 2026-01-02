<?php

namespace App\Livewire\Components\Documentos\Historial;

use App\Models\Estado;
use App\Services\Documento\DocumentoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Tabla extends Component
{
    use WithPagination;

    #[Url('buscar')]
    public $buscar = '';
    #[Url('fecha_inicio')]
    public $fechaInicio = '';
    #[Url('fecha_fin')]
    public $fechaFin = '';
    #[Url('estado')]
    public $idEstadoFiltro = '';
    public $estados = [];

    protected DocumentoService $documentoService;
    protected $paginationTheme = 'bootstrap';

    public function boot()
    {
        $this->documentoService = resolve(DocumentoService::class);
    }

    public function mount()
    {
        // Cargar todos los estados disponibles
        $this->estados = Estado::pluck('nombre_estado', 'id_estado')->toArray();
    }

    public function updating($key)
    {
        if (in_array($key, ['buscar', 'fechaInicio', 'fechaFin', 'idEstadoFiltro'])) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros()
    {
        $this->buscar = '';
        $this->fechaInicio = '';
        $this->fechaFin = '';
        $this->idEstadoFiltro = '';
        $this->resetPage();
    }

    #[Computed]
    public function historial()
    {
        $usuario = Auth::user();
        $idAreaActual = $usuario->persona->id_area ?? null;

        if (!$idAreaActual) {
            return collect()->paginate(10);
        }

        return $this->documentoService->obtenerHistorialMovimientosArea(
            $idAreaActual,
            $this->buscar,
            $this->fechaInicio,
            $this->fechaFin,
            $this->idEstadoFiltro
        );
    }

    public function render()
    {
        return view('livewire.components.documentos.historial.tabla');
    }
}
