<?php

namespace App\Livewire\Documentos\Pendientes;

use App\Services\Documento\DocumentoService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\On;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public $modeloDocumento = null;

    protected DocumentoService $documentoService;

    public function __construct()
    {
        $this->documentoService = resolve(DocumentoService::class);
    }

    #[On('abrirModalDetalleDocumento')]
    public function abrirModalDetalleDocumento($id_documento)
    {
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento, ['estado']);

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-detalle-documento', 'show');
    }

    public function modalDocumento($nombre, $accion)
    {
        $this->dispatch(
            'modal',
            nombre: $nombre,
            accion: $accion
        );
    }

    public function render()
    {
        return view('livewire.documentos.pendientes.index');
    }
}
