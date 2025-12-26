<?php

namespace App\Livewire\Components\Documentos\Historial;

use App\Services\Documento\DocumentoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Tabla extends Component
{
    use WithPagination;

    public $buscar = '';

    protected DocumentoService $documentoService;

    public function boot()
    {
        $this->documentoService = resolve(DocumentoService::class);
    }

    public function updating($key)
    {
        if ($key === 'buscar') {
            $this->resetPage();
        }
    }

    #[Computed]
    public function historial()
    {
        $usuario = Auth::user();
        $idAreaActual = $usuario->persona->id_area ?? null;

        if (!$idAreaActual) {
            return collect()->paginate(10);
        }

        return $this->documentoService->obtenerHistorialMovimientosArea($idAreaActual, $this->buscar);
    }

    public function render()
    {
        return view('livewire.components.documentos.historial.tabla');
    }
}
